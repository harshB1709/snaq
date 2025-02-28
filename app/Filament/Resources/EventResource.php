<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RelationManager;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists\Infolist;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-c-calendar-date-range';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Event Tabs')
                    ->tabs([
                        Tabs\Tab::make('Event Details')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Event Name')
                                    ->required()
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                TextInput::make('slug')
                                    ->label('Event Slug')
                                    ->required(),
                                DatePicker::make('start_date')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                                DatePicker::make('end_date')
                                    ->native(false)
                                    ->closeOnDateSelection()
                                    ->afterOrEqual('start_date'),
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                                FileUpload::make('background_img_url')
                                    ->label('Background Image')
                                    ->image()
                                    ->imageEditor(),
                                MarkdownEditor::make('home_content')
                                    ->fileAttachmentsDirectory('attachments')
                                    ->columnSpanFull()
                            ])
                            ->icon('heroicon-o-presentation-chart-bar')
                            ->iconPosition(IconPosition::After)
                            ->columns(2),
                        Tabs\Tab::make('Event Settings')
                            ->schema([
                                Repeater::make('eventSettings')
                                    ->label('')
                                    ->relationship('appSettings')
                                    ->disableItemDeletion()
                                    ->disableItemCreation()
                                    ->schema([
                                        Placeholder::make('setting_label')
                                            ->label('')
                                            ->content(fn ($get) => Str::replace('_', ' ', Str::title($get('key'))))
                                            ->columnSpanFull(),
                                        Hidden::make('key'),
                                        Toggle::make('value')
                                            ->label('Enable')
                                            ->inline(false)
                                            ->columnSpan(1),
                                        TextInput::make('message')
                                            ->label('Message')
                                            ->columnSpan(6),
                                    ])
                                    ->columns(7)
                                    ->columnSpanFull()
                                    ->afterStateHydrated(function ($state, callable $set, $record) {
                                        $defaultSettings = collect(
                                            [
                                                ['key' => 'app_status', 'value' => false, 'message' => ''],
                                                ['key' => 'player_registration', 'value' => true, 'message' => ''],
                                                ['key' => 'show_leaderboard', 'value' => false, 'message' => ''],
                                                ['key' => 'allow_replay', 'value' => false, 'message' => ''],
                                                ['key' => 'force_mobile', 'value' => true, 'message' => ''],
                                            ]
                                        )->keyBy('key');
                                        $existingSettings = collect($state)->keyBy('key')->filter(fn ($v, $k) => !empty($v['key']));

                                        $mergedSettings = $defaultSettings->merge($existingSettings)->values();
                                        $set('eventSettings', $mergedSettings->toArray());
                                    })
                            ])
                            ->icon('heroicon-m-cog-8-tooth')
                            ->iconPosition(IconPosition::After),
                        Tabs\Tab::make('Players')
                            ->schema([
                                \Njxqlus\Filament\Components\Forms\RelationManager::make()->manager(RelationManagers\PlayerRelationManager::class)->lazy(true)
                            ])
                            ->hiddenOn('create')
                            ->icon('heroicon-m-user-group')
                            ->iconPosition(IconPosition::After)
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Event name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Event Slug')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('start_date')
                    ->sortable()
                    ->searchable()
                    ->date(),
                TextColumn::make('end_date')
                    ->sortable()
                    ->searchable()
                    ->date(),
                ToggleColumn::make('is_active')
                    ->label('Active')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('self_register')
                    ->url(fn ($record) => route('player.register', ['event' => $record]), shouldOpenInNewTab: true)
                    ->icon('heroicon-s-user-plus')
                    ->color('info'),
                Tables\Actions\Action::make('leaderboard')
                    ->url(fn ($record) => route('leaderboard', ['event' => $record]), shouldOpenInNewTab: true)
                    ->icon('heroicon-m-trophy')
                    ->color('gray')
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
            // RelationManagers\PlayerRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([       
            Infolists\Components\Tabs::make()->tabs([
                Infolists\Components\Tabs\Tab::make('Versions')->schema([
                    \Njxqlus\Filament\Components\Infolists\RelationManager::make()->manager(RelationManagers\PlayerRelationManager::class)->lazy(false)
                ])
            ])
        ]);
    }
}
