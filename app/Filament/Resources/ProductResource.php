<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Shop / Catalog';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Product Details')
                    ->tabs([
                        // Tab 1: Basic Info
                        Forms\Components\Tabs\Tab::make('General Information')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->required()
                                            ->maxLength(191)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                                if ($operation === 'create') {
                                                    $set('slug', Str::slug($state));
                                                    $set('sku', 'SKU-' . strtoupper(Str::random(6)));
                                                }
                                            }),
                                        Forms\Components\TextInput::make('slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(191),
                                    ]),
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('sku')
                                            ->label('Product SKU')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(191),
                                        Forms\Components\Select::make('category_id')
                                            ->label('Category')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Forms\Components\Select::make('brand_id')
                                            ->label('Brand')
                                            ->relationship('brand', 'name')
                                            ->searchable()
                                            ->preload(),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Active in Storefront')
                                            ->default(true)
                                            ->required(),
                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Featured on Home / Flash Sale')
                                            ->default(false),
                                    ]),
                            ]),

                        // Tab 2: Pricing & Stock
                        Forms\Components\Tabs\Tab::make('Pricing & Inventory')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('price')
                                            ->label('Regular Price (BDT)')
                                            ->required()
                                            ->numeric()
                                            ->prefix('৳'),
                                        Forms\Components\TextInput::make('sale_price')
                                            ->label('Special Sale Price (BDT)')
                                            ->numeric()
                                            ->prefix('৳')
                                            ->helperText('Leave empty if no promotion active'),
                                        Forms\Components\TextInput::make('stock')
                                            ->label('Inventory Stock Quantity')
                                            ->required()
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0),
                                    ]),
                            ]),

                        // Tab 3: Descriptions & Specs
                        Forms\Components\Tabs\Tab::make('Descriptions & Specs')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\Textarea::make('short_description')
                                    ->label('Short Summary (Displayed beside buy box)')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('description')
                                    ->label('Full Product Description & Highlights')
                                    ->columnSpanFull(),
                                Forms\Components\KeyValue::make('specifications')
                                    ->label('Technical Specifications')
                                    ->keyLabel('Specification Name (e.g. Battery, Display, Material)')
                                    ->valueLabel('Specification Value (e.g. 5000 mAh, 6.7 inch AMOLED, Leather)')
                                    ->columnSpanFull(),
                            ]),

                        // Tab 4: Image Gallery
                        Forms\Components\Tabs\Tab::make('Image Gallery')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\Repeater::make('images')
                                    ->relationship('images')
                                    ->schema([
                                        Forms\Components\FileUpload::make('image_path')
                                            ->label('Image File')
                                            ->image()
                                            ->directory('products')
                                            ->visibility('public')
                                            ->required(),
                                        Forms\Components\Toggle::make('is_primary')
                                            ->label('Primary Thumbnail')
                                            ->default(false),
                                        Forms\Components\TextInput::make('sort_order')
                                            ->label('Sort Order')
                                            ->numeric()
                                            ->default(0),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(1)
                                    ->addActionLabel('Add Product Image')
                                    ->reorderable('sort_order')
                                    ->columnSpanFull(),
                            ]),

                        // Tab 5: Variants
                        Forms\Components\Tabs\Tab::make('Variants')
                            ->icon('heroicon-o-squares-plus')
                            ->schema([
                                Forms\Components\Repeater::make('variants')
                                    ->relationship('variants')
                                    ->schema([
                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                Forms\Components\TextInput::make('sku')
                                                    ->label('Variant SKU')
                                                    ->required(),
                                                Forms\Components\TextInput::make('price')
                                                    ->label('Price Override (BDT)')
                                                    ->numeric()
                                                    ->prefix('৳'),
                                                Forms\Components\TextInput::make('sale_price')
                                                    ->label('Sale Price Override')
                                                    ->numeric()
                                                    ->prefix('৳'),
                                                Forms\Components\TextInput::make('stock')
                                                    ->label('Variant Stock')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->required(),
                                            ]),
                                        Forms\Components\FileUpload::make('image_path')
                                            ->label('Variant Image')
                                            ->image()
                                            ->directory('products/variants')
                                            ->visibility('public'),
                                        Forms\Components\KeyValue::make('attributes')
                                            ->label('Variant Attributes')
                                            ->keyLabel('Attribute (e.g. color, size)')
                                            ->valueLabel('Option (e.g. Red, XL)'),
                                    ])
                                    ->collapsible()
                                    ->addActionLabel('Add Product Variant')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('primaryImage.image_path')
                    ->label('Thumbnail')
                    ->square()
                    ->size(50),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('BDT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->money('BDT')
                    ->color('danger')
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('stock')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state > 10 => 'success',
                        $state > 0 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
                Tables\Columns\ToggleColumn::make('is_featured')
                    ->label('Featured'),
                Tables\Columns\TextColumn::make('sold_count')
                    ->label('Sold')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('brand_id')
                    ->label('Brand')
                    ->relationship('brand', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
                Tables\Filters\Filter::make('in_stock')
                    ->label('In Stock Only')
                    ->query(fn ($query) => $query->where('stock', '>', 0)),
                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Out of Stock')
                    ->query(fn ($query) => $query->where('stock', '<=', 0)),
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
