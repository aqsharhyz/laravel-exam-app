<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    // protected static string $view = 'filament.pages.edit-profile';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                FileUpload::make('profile_picture')
                    ->label('Profile Picture')
                    ->directory('images/profile_pictures')
                    ->imageEditor()
                    ->imageEditorAspectRatios(['1:1'])
                    //! red
                    ->imageEditorEmptyFillColor('#ff0000')
                    // ->maxFileSize(2048)
                    // ->visibility('public')
                    ->avatar(),
            ]);
    }
}
