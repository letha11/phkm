<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Prescription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\FiltersLayout;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationGroup = 'Financial Management';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('prescription_id')
                    ->label('Prescription')
                    ->options(Prescription::with(['patient', 'doctor'])
                        ->get()
                        ->mapWithKeys(function ($prescription) {
                            return [$prescription->id => "#{$prescription->id} - {$prescription->patient->name} (Dr. {$prescription->doctor->name})"];
                        }))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('invoice_number')
                    ->label('Invoice Number')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('issue_date')
                    ->label('Issue Date')
                    ->required()
                    ->default(now()),
                Forms\Components\TextInput::make('subtotal_medicines')
                    ->label('Subtotal Medicines')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->step(0.01)
                    ->required(),
                Forms\Components\TextInput::make('consultation_fee')
                    ->label('Consultation Fee')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->step(0.01)
                    ->required(),
                Forms\Components\TextInput::make('ppn_amount')
                    ->label('PPN Amount')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->step(0.01)
                    ->required(),
                Forms\Components\TextInput::make('grand_total')
                    ->label('Grand Total')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->step(0.01)
                    ->required(),
                Forms\Components\Select::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'cash' => 'Cash',
                        'credit_card' => 'Credit Card',
                        'debit_card' => 'Debit Card',
                        'bank_transfer' => 'Bank Transfer',
                        'e_wallet' => 'E-Wallet',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prescription.patient.name')
                    ->label('Patient')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prescription.doctor.name')
                    ->label('Doctor')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Issue Date')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal_medicines')
                    ->label('Medicines')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('consultation_fee')
                    ->label('Consultation')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ppn_amount')
                    ->label('PPN')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'credit_card' => 'warning',
                        'debit_card' => 'info',
                        'bank_transfer' => 'primary',
                        'e_wallet' => 'secondary',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'cash' => 'Cash',
                        'credit_card' => 'Credit Card',
                        'debit_card' => 'Debit Card',
                        'bank_transfer' => 'Bank Transfer',
                        'e_wallet' => 'E-Wallet',
                    ]),
                Tables\Filters\Filter::make('grand_total')
                    ->form([
                        Forms\Components\TextInput::make('from')
                            ->label('Total From')
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('to')
                            ->label('Total To')
                            ->numeric()
                            ->prefix('Rp'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $amount): Builder => $query->where('grand_total', '>=', $amount),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $amount): Builder => $query->where('grand_total', '<=', $amount),
                            );
                    }),
                Tables\Filters\Filter::make('issue_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Issue Date From'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Issue Date Until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('issue_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('issue_date', '<=', $date),
                            );
                    }),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('issue_date', 'desc');
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
} 