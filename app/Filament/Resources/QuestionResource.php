<?php

namespace App\Filament\Resources;

use App\Enums\Difficulty;
use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('question')
                    ->label('Question')
                    ->required()
                    ->columnSpanFull(),

                Repeater::make('options')
                    ->simple(
                        TextInput::make('option')
                            ->hiddenLabel()
                            ->required(),
                    )
                    ->minItems(2)
                    ->maxItems(4)
                    ->default(['', ''])
                    ->live(debounce: 500),

                Select::make('answer')
                    ->label('Correct Answer')
                    ->options(fn (Get $get) => collect(array_values($get('options')))->mapWithKeys(function($v) {
                            $val = array_values($v)[0];
                            return [$val => $val];
                        })->sort())
                    ->required(),

                Select::make('difficulty')
                    ->label('Difficulty Level')
                    ->options(collect(Difficulty::cases())->mapWithKeys(fn ($v) => [$v->name => ucfirst($v->value)]))
                    ->default('easy')
                    ->required(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('question')
                    ->label('Question')
                    ->limit(60)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('answer')
                    ->label('Correct Answer')
                    ->sortable(),

                TextColumn::make('difficulty')
                    ->formatStateUsing(fn (Difficulty $state): string => ucfirst($state->value))
                    ->badge()
                    ->color(fn (Difficulty $state): string => match ($state->name) {
                        'easy' => 'success',
                        'medium' => 'warning',
                        'hard' => 'danger',
                    })
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('difficulty')
                ->options(collect(Difficulty::cases())->mapWithKeys(fn ($v) => [$v->name => ucfirst($v->value)])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
