<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Services\EnrollmentService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')->searchable()->copyable(),
                TextColumn::make('user.phone')->label('Student')->searchable()->placeholder('—'),
                TextColumn::make('course.title')->label('Item')->limit(40)->placeholder('Zirak Plus')
                    ->description(fn (Order $record) => $record->isSubscription() ? 'Monthly plan' : null),
                TextColumn::make('total')->formatStateUsing(fn ($state, Order $record) => $record->currency === 'USD' ? '$'.number_format($state / 100, 2) : number_format($state).' AFN'),
                TextColumn::make('coupon.code')->label('Coupon')->placeholder('—'),
                TextColumn::make('gateway')->badge(),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state?->value ?? $state) {
                    'paid' => 'success', 'pending' => 'warning', 'failed' => 'danger', default => 'gray'
                }),
                TextColumn::make('gateway_transaction_id')->label('Transaction')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('paid_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('kind')->label('Type')->options(['course' => 'Course', 'subscription' => 'Zirak Plus']),
                SelectFilter::make('status')->options(['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed', 'cancelled' => 'Cancelled', 'refunded' => 'Refunded']),
                SelectFilter::make('gateway')->options(['hesabpay' => 'HesabPay', 'coupon' => 'Coupon', 'manual' => 'Manual']),
            ])
            ->recordActions([
                Action::make('markPaid')->label('Mark paid')->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (Order $record) => ! $record->isPaid())
                    ->requiresConfirmation()
                    ->modalDescription('Use only after you have confirmed the money arrived (for example in the HesabPay dashboard or by bank transfer). The student gets access immediately.')
                    ->action(function (Order $record) {
                        app(EnrollmentService::class)->markPaid($record, $record->gateway_transaction_id);
                        Notification::make()->title('Order marked paid')->success()->send();
                    }),
            ]);
    }
}
