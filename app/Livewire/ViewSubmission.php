<?php

namespace App\Livewire;

use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Livewire\Component;

class ViewSubmission extends Component implements HasInfolists
{
    use InteractsWithInfolists;

    public function render()
    {
        return view('livewire.view-submission');
    }

    public function submissionInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->submission)
            ->schema([
                // ...
            ]);
    }
}
