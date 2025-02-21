<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

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

class PlayerRelationManager extends RelationManager
{
    protected static string $relationship = 'players';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('display_name')
                    ->maxLength(255),
                TextInput::make('email')
                    ->required()
                    ->email(),
                TextInput::make('phone')
                    ->label('Phone No.')
                    ->required()
                    ->tel(),
                Hidden::make('invite_expires_at')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('display_name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('display_name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('invite_expires_at')->since(),
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
                    ->action(function($data, Player $record) {
                        $record->game()?->delete();
                        $record->invite_expires_at = now()->addMinutes(config('app.game.invite_validity_mins'));
                        $record->save();

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
                    ->color('secondary')
                    ->icon('heroicon-o-envelope')
                    ->action(function($data, Player $record) {
                        $record->notify(new GameInvite());

                        Notification::make()
                            ->title('Invite resent!')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
