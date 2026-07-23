<?php

namespace App\Filament\Admin\Resources\Modifiers;

use App\Filament\Admin\Resources\Modifiers\Pages\CreateModifier;
use App\Filament\Admin\Resources\Modifiers\Pages\EditModifier;
use App\Filament\Admin\Resources\Modifiers\Pages\ListModifiers;
use App\Filament\Admin\Resources\Modifiers\Schemas\ModifierForm;
use App\Filament\Admin\Resources\Modifiers\Tables\ModifiersTable;
use App\Models\Modifier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ModifierResource extends Resource
{
    protected static ?string $model = Modifier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ModifierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModifiersTable::configure($table);
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
            'index' => ListModifiers::route('/'),
            'create' => CreateModifier::route('/create'),
            'edit' => EditModifier::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        if ($productId = request()->query('product')) {
            $query->whereHas('modifierGroup.products', function ($q) use ($productId) {
                $q->where('products.id', $productId);
            });
        }
        
        return $query;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
