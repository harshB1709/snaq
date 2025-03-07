<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class AvatarSelector extends Field
{
    protected string $view = 'forms.components.avatar-selector';

    protected array $avatarOptions = [];

    public function avatarOptions(array $options): static
    {
        $this->avatarOptions = $options;

        return $this;
    }

    public function getAvatarOptions(): array
    {
        return $this->avatarOptions;
    }
}
