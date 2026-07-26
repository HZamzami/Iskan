<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Tables\UsersTable;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->hidden(fn (User $record): bool => $record->is(Filament::auth()->user()))
                ->before(function (DeleteAction $action, User $record): void {
                    if (UsersTable::isLastAdmin($record)) {
                        UsersTable::notifyLastAdmin();
                        $action->cancel();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var User $user */
        $user = $this->getRecord();

        return array_merge($data, UserResource::accessFormState($user));
    }

    protected function beforeSave(): void
    {
        /** @var User $user */
        $user = $this->getRecord();

        if (! ($this->data['is_admin'] ?? false) && UsersTable::isLastAdmin($user)) {
            UsersTable::notifyLastAdmin();

            $this->halt();
        }
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update(Arr::only($data, ['name', 'email', 'password']));

        UserResource::syncAccess($record, $data);

        return $record;
    }
}
