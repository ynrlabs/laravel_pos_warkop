<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\Outlet;
use App\Models\User;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Manajemen Staff';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Staff';

    protected static ?string $pluralModelLabel = 'Manajemen Staff';

    public static function getNavigationGroup(): ?string
    {
        return 'Pengaturan';
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedUsers;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Dasar')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('employee_id')
                                ->label('NIK / ID Karyawan')
                                ->unique(ignoreRecord: true)
                                ->maxLength(20)
                                ->placeholder('Contoh: KRY001'),

                            Select::make('outlet_id')
                                ->label('Outlet')
                                ->options(Outlet::pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->default(fn() => auth()->user()->outlet_id ?? null)
                                ->disabled(fn() => auth()->user()->role !== 'super_admin'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nama Lengkap')
                                ->required()
                                ->maxLength(100),

                            TextInput::make('email')
                                ->label('Email')
                                ->email()
                                ->unique(ignoreRecord: true)
                                ->maxLength(100)
                                ->placeholder('opsional@email.com'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('phone')
                                ->label('No. Telepon / WhatsApp')
                                ->tel()
                                ->maxLength(20)
                                ->placeholder('08xx-xxxx-xxxx'),

                            Select::make('role')
                                ->label('Role / Jabatan')
                                ->options([
                                    'super_admin' => 'Super Admin',
                                    'manager' => 'Manager',
                                    'kasir' => 'Kasir',
                                    'barista' => 'Barista',
                                    'koki' => 'Koki',
                                    'gudang' => 'Gudang',
                                ])
                                ->required()
                                ->default('kasir')
                                ->native(false)
                                ->disabled(fn() => auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'manager'),
                        ]),
                    ])
                    ->columns(2),

                Section::make('Keamanan & Akses')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('password')
                                ->label('Password')
                                ->password()
                                ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                ->dehydrated(fn($state) => filled($state))
                                ->required(fn(string $context): bool => $context === 'create')
                                ->minLength(8)
                                ->maxLength(255)
                                ->revealable()
                                ->helperText('Minimal 8 karakter. Kosongkan untuk tidak mengubah password.'),

                            TextInput::make('password_confirmation')
                                ->label('Konfirmasi Password')
                                ->password()
                                ->dehydrated(false)
                                ->required(fn(string $context): bool => $context === 'create')
                                ->same('password')
                                ->revealable()
                                ->visible(fn(string $context): bool => $context === 'create' || filled(request()->input('password'))),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('pin')
                                ->label('PIN (4-6 digit)')
                                ->password()
                                ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                                ->dehydrated(fn($state) => filled($state))
                                ->numeric()
                                ->minLength(4)
                                ->maxLength(6)
                                ->revealable()
                                ->placeholder('Kosongkan jika tidak diubah')
                                ->helperText('PIN untuk aksi sensitif di POS (void, refund, dll). Kosongkan jika tidak diubah.'),

                            TextInput::make('pin_confirmation')
                                ->label('Konfirmasi PIN')
                                ->password()
                                ->dehydrated(false)
                                ->same('pin')
                                ->revealable()
                                ->visible(fn(string $context): bool => filled(request()->input('pin'))),
                        ]),

                        Toggle::make('two_factor_enabled')
                            ->label('Aktifkan 2FA')
                            ->helperText('Aktifkan 2FA untuk akses admin/manager (butuh Google Authenticator)')
                            ->visible(fn() => auth()->user()->role === 'super_admin' || auth()->user()->role === 'manager'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Status & Preferensi')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_active')
                                ->label('Aktif')
                                ->default(true)
                                ->inline(false),

                            Select::make('preferences.theme')
                                ->label('Tema')
                                ->options([
                                    'light' => 'Terang (Siang)',
                                    'dark' => 'Gelap (Malam)',
                                    'system' => 'Ikuti Sistem',
                                ])
                                ->default('system')
                                ->native(false),
                        ]),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_id')
                    ->label('NIK')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color('primary'),

                BadgeColumn::make('role')
                    ->label('Role')
                    ->colors([
                        'danger' => 'super_admin',
                        'warning' => 'manager',
                        'primary' => 'kasir',
                        'info' => 'barista',
                        'success' => 'koki',
                        'gray' => 'gudang',
                    ])
                    ->icons([
                        'super_admin' => 'heroicon-o-shield-check',
                        'manager' => 'heroicon-o-user-circle',
                        'kasir' => 'heroicon-o-currency-dollar',
                        'barista' => 'heroicon-o-cup-hot',
                        'koki' => 'heroicon-o-fire',
                        'gudang' => 'heroicon-o-cube',
                    ])
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Status')
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('last_login_at')
                    ->label('Login Terakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Belum pernah login'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name')
                    ->default(fn() => auth()->user()->outlet_id ?? null)
                    ->disabled(fn() => auth()->user()->role !== 'super_admin'),

                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'manager' => 'Manager',
                        'kasir' => 'Kasir',
                        'barista' => 'Barista',
                        'koki' => 'Koki',
                        'gudang' => 'Gudang',
                    ])
                    ->native(false),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->disabled(fn(User $record): bool => $record->id === auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->disabled(fn(Collection $records): bool => $records->contains(auth()->id())),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
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
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->role !== 'super_admin') {
            $query->where('outlet_id', auth()->user()->outlet_id);
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'super_admin' || auth()->user()?->role === 'manager';
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->role === 'super_admin' || auth()->user()?->role === 'manager';
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        
        if ($user->role === 'super_admin') {
            return true;
        }
        
        if ($user->role === 'manager') {
            // Manager hanya bisa edit user di outlet yang sama
            return $record->outlet_id === $user->outlet_id;
        }

        return false;
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        
        // Super admin bisa hapus siapa saja kecuali dirinya sendiri
        if ($user->role === 'super_admin') {
            return $record->id !== $user->id;
        }
        
        // Manager hanya bisa hapus user di outlet yang sama, kecuali manager/super_admin lain
        if ($user->role === 'manager') {
            return $record->outlet_id === $user->outlet_id 
                && $record->id !== $user->id
                && !in_array($record->role, ['super_admin', 'manager']);
        }

        return false;
    }
}