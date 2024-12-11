<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamResource\Pages;
use App\Filament\Resources\ExamResource\RelationManagers;
use App\Models\Exam;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil';

    protected static ?string $navigationLabel = 'Exam';

    protected static ?string $modelLabel = 'Exam';

    protected static ?string $navigationGroup = 'Lesson Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('lesson_id')
                    ->relationship('lesson', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DateTimePicker::make('start_time')
                    ->required(),
                Forms\Components\DateTimePicker::make('end_time')
                    ->required(),
                Forms\Components\TextInput::make('duration')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('passing_grade')
                    ->required()
                    ->numeric()
                    ->default(-1),
                Forms\Components\TextInput::make('total_score')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('hide_score')
                    ->required(),
                Forms\Components\Toggle::make('hide_correct_answers')
                    ->required(),
                Forms\Components\Toggle::make('multiple_attempts')
                    ->required(),

                Forms\Components\Repeater::make('Questions')
                    ->label('Questions')
                    // ->recordComponent(Forms\Components\OrderDetailComponent::class)
                    ->addActionLabel('Add Question')
                    // ->maxItems(10)
                    ->minItems(1)
                    ->relationship('questions')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\RichEditor::make('question_text')
                            ->required()
                            ->columnSpanFull()
                            ->disableToolbarButtons([
                                'codeBlock',
                                'attachFiles',
                            ])
                            ->disableGrammarly()
                            ->maxLength(255),
                    ]),

                // Forms\Components\Repeater::make('Options')
                //     ->label('Options')
                //     // ->recordComponent(Forms\Components\Option::class)
                //     ->addActionLabel('Add Option')
                //     ->minItems(2)
                //     ->maxItems(10)
                //     ->relationship('questions.options')
                //     ->columnSpanFull()
                //     ->schema([
                //         Forms\Components\TextInput::make('option_text')
                //             ->required()
                //             ->maxLength(255),
                //         Forms\Components\Toggle::make('is_correct')
                //             ->required(),
                //     ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lesson.title')
                    ->label('Lesson')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('passing_grade')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('hide_score')
                    ->boolean(),
                Tables\Columns\IconColumn::make('hide_correct_answers')
                    ->boolean(),
                Tables\Columns\IconColumn::make('multiple_attempts')
                    ->boolean(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
