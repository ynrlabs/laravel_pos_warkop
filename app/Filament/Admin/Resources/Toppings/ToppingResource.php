<?php

namespace App\Filament\Admin\Resources\Toppings;

use App\Filament\Admin\Resources\Toppings\Pages\CreateTopping;
use App\Filament\Admin\Resources\Toppings\Pages\EditTopping;
use App\Filament\Admin\Resources\Toppings\Pages\ListToppings;
use App\Filament\Admin\Resources\Toppings\Schemas\ToppingForm;
use App\Filament\Admin\Resources\Toppings\Tables\ToppingsTable;
use App\Models\Topping;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ToppingResource extends Resource
{
    protected static ?string $model = Topping::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ToppingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ToppingsTable::configure($table);
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
            'index' => ListToppings::route('/'),
            'create' => CreateTopping::route('/create'),
            'edit' => EditTopping::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        if ($productId = request()->query('product')) {
            $query->whereHas('products', function ($q) use ($productId) {
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
