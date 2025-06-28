<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Filament\Tables\Enums\FiltersLayout;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)) // hash the password
                    ->dehydrated(fn ($state) => filled($state)) // only save if the password is filled
                    ->required(fn (string $context): bool => $context === 'create'), // only required if the user is being created
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\DatePicker::make('date_of_birth')
                    ->required(),
                Forms\Components\Select::make('roles')
                    ->label('Role')
                    ->options(Role::all()->pluck('name', 'id'))
                    ->relationship('roles', 'name')
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('username'),
                Tables\Columns\TextColumn::make('roles')
                    ->label('Role')
                    ->formatStateUsing(function ($record) {
                        return $record->roles->pluck('name')->join(', ');
                    }),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date('d-m-Y'),
            ])
            ->filters([
                Tables\Filters\Filter::make('username')
                    ->form([
                        TextInput::make('username'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['username'] == null) {
                            return $query;
                        }

                        return $query->where('username', 'like', '%' . $data['username'] . '%');
                    }),
                Tables\Filters\Filter::make('name')
                    ->form([
                        TextInput::make('name'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['name'] == null) {
                            return $query;
                        }

                        return $query->where('name', 'like', '%' . $data['name'] . '%');
                    }),
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Role')
                    ->options(Role::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] == null) {
                            return $query;
                        }

                        return $query->whereHas('roles', function (Builder $query) use ($data) {
                            $query->whereIn('id', $data);
                        });
                    }),

            ], layout: FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
