<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OutletResource\Pages;
use App\Models\Outlet;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use BackedEnum;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class OutletResource extends Resource
{
    protected static ?string $model = Outlet::class;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedBuildingStorefront;
    }

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Master Data';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Dasar')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Outlet')
                            ->required()
                            ->maxLength(100),
                        
                        TextInput::make('code')
                            ->label('Kode Outlet')
                            ->required()
                            ->maxLength(20)
                            ->unique(Outlet::class, 'code', ignoreRecord: true)
                            ->helperText('Kode singkat, misal: WPK01'),
                        
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->maxLength(500),
                        
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(20),
                        
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(100),
                        
                        TextInput::make('npwp')
                            ->label('NPWP')
                            ->maxLength(30)
                            ->helperText('Nomor Pokok Wajib Pajak untuk struk'),
                    ])->columns(2),

                Section::make('Branding & Struk')
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo Outlet')
                            ->image()
                            ->directory('outlets/logos')
                            ->maxSize(512)
                            ->columnSpanFull(),
                        
                        Textarea::make('receipt_footer')
                            ->label('Footer Struk')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Teks di bagian bawah struk (promo, IG, dll)'),
                    ])->columns(1),

                Section::make('Jam Operasional')
                    ->schema([
                        TimePicker::make('operational_start')
                            ->label('Buka')
                            ->default('06:00')
                            ->seconds(false),
                        
                        TimePicker::make('operational_end')
                            ->label('Tutup')
                            ->default('22:00')
                            ->seconds(false),
                    ])->columns(2),

                Section::make('Status & Pengaturan')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        
                        KeyValue::make('settings')
                            ->label('Pengaturan Tambahan (JSON)')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addActionLabel('Tambah'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->size(40)
                    ->circular(),
                
                TextColumn::make('name')
                    ->label('Nama Outlet')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                
                TextColumn::make('code')
                    ->label('Kode')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('operational_start')
                    ->label('Buka')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('H:i') : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('operational_end')
                    ->label('Tutup')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('H:i') : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                
                TextColumn::make('users_count')
                    ->label('User')
                    ->counts('users')
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
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
            'index' => Pages\ListOutlets::route('/'),
            'create' => Pages\CreateOutlet::route('/create'),
            'edit' => Pages\EditOutlet::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        if (auth()->user()->role !== 'super_admin') {
            $query->where('id', auth()->user()->outlet_id);
        }
        
        return $query;
    }
}