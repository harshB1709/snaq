<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Forms\Components\AvatarSelector;
use App\Models\Player;
use App\Notifications\GameInvite;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Closure;
use Filament\Forms\Get;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PlayerRelationManager extends RelationManager
{
    protected static string $relationship = 'players';

    public function form(Form $form): Form
    {
        $avatars = [];

        for ($i=1; $i < 25; $i++) { 
            $avatars[] = url("/images/avatars/snake_{$i}.png");
        }
        return $form
            ->schema([
                AvatarSelector::make('avatar')
                    ->label('Select Your Avatar')
                    ->avatarOptions($avatars)
                    ->default($avatars[array_rand($avatars)]),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('display_name')
                    ->maxLength(255),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('event_id', $this->getOwnerRecord()?->id)),
                Hidden::make('invite_expires_at')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('display_name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('display_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('avatar')
                    ->circular()
                    ->checkFileExistence(false)
                    ->extraImgAttributes(
                        ['style' => 'object-fit: contain; background: #a4cbb4']
                    ),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invite_expires_at')
                    ->since()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('games_count')
                    ->label('Games Played')
                    ->sortable(),
                Tables\Columns\TextColumn::make('game.score')
                    ->label('Score')
                    ->searchable()
                    ->sortable(
                        query: fn (Builder $query, string $direction): Builder => 
                        $query->leftJoin('games', 'players.id', '=', 'games.player_id')
                            ->select('players.*', 'games.score')
                            ->orderByRaw("games.score {$direction} NULLS LAST")
                    )
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function(Player $player) {
                        $player->notify(new GameInvite());
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('reset_game')
                    ->label('Reset Game')
                    ->accessSelectedRecords()
                    ->requiresConfirmation()
                    ->modalHeading('Reset Game')
                    ->modalSubheading('Are you sure you want to reset the game for this player? This action will delete the current game score for the player.')
                    ->modalSubmitActionLabel('Reset')
                    ->color('primary')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->button()
                    ->action(function($data, Player $record, Collection $selectedRecords) {
                        $selectedRecords->push($record);
                        $selectedRecords = $selectedRecords->unique('id');
                        
                        foreach ($selectedRecords as $lineRecord) {
                            $lineRecord->game()?->delete();
                            $lineRecord->invite_expires_at = now()->addMinutes(config('app.game.invite_validity_mins'));
                            $lineRecord->save();
                        }

                        Notification::make()
                            ->title('Game has been reset!')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('resend_invite')
                    ->label('Resend Invite')
                    ->accessSelectedRecords()
                    ->requiresConfirmation()
                    ->modalHeading('Resend Invite')
                    ->modalSubheading('Are you sure you want to resend the invite to this player?')
                    ->modalSubmitActionLabel('Resend')
                    ->color('info')
                    ->icon('heroicon-o-envelope')
                    ->button()
                    ->action(function($data, Player $record, Collection $selectedRecords) {
                        $selectedRecords->push($record);
                        $selectedRecords = $selectedRecords->unique('id');

                        foreach ($selectedRecords as $lineRecord) {
                            $lineRecord->notify(new GameInvite());
                        }

                        Notification::make()
                            ->title('Invite resent!')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('game_url')
                    ->label('Game URL')
                    ->url(fn ($record) => URL::signedRoute('game', ['player' => $record->id, 'event' => $record?->event?->slug]), shouldOpenInNewTab: true)
                    ->icon('heroicon-c-link')
                    ->color('gray')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
