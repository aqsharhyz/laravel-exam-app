<?php

namespace App\Filament\Resources\SubmissionResource\Pages;

use App\Filament\Resources\SubmissionResource;
use Filament\Actions;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSubmission extends ViewRecord
{
    protected static string $resource = SubmissionResource::class;

    protected static string $view = 'filament.resources.submissions.pages.view-submission';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Submission Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('score'),
                        TextEntry::make('is_submitted'),
                        TextEntry::make('exam_id'),
                        TextEntry::make('enroll_id'),
                    ]),
                // Section::make('Question Answers', []),

                RepeatableEntry::make('answers')
                    ->schema([
                        TextEntry::make('question.question_text'),
                        RepeatableEntry::make('options')
                            ->schema([
                                TextEntry::make('option_text'),
                            ]),

                    ]),
            ]);
    }
}
