<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exports\ReportExport;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;

class ReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $title           = 'Laporan & Statistik';
    protected static ?int    $navigationSort  = 3;

    protected static string $view = 'filament.pages.report-page';

    // ── State ─────────────────────────────────────────────────────────────────

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to'   => now()->toDateString(),
        ]);
    }

    // ── Form (date range filter) ──────────────────────────────────────────────

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Periode')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        DatePicker::make('date_from')
                            ->label('Dari Tanggal')
                            ->required()
                            ->displayFormat('d/m/Y')
                            ->maxDate(fn () => $this->data['date_to'] ?? now()->toDateString())
                            ->default(now()->startOfMonth()->toDateString()),

                        DatePicker::make('date_to')
                            ->label('Sampai Tanggal')
                            ->required()
                            ->displayFormat('d/m/Y')
                            ->minDate(fn () => $this->data['date_from'] ?? now()->startOfMonth()->toDateString())
                            ->maxDate(now()->toDateString())
                            ->default(now()->toDateString()),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    public function applyFilter(): void
    {
        $this->form->validate();
        // Livewire sudah reaktif — computed properties akan otomatis refresh
    }

    public function exportExcel(): mixed
    {
        $this->form->validate();

        $dateFrom = $this->data['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo   = $this->data['date_to']   ?? now()->toDateString();

        try {
            return (new ReportExport($dateFrom, $dateTo))->download();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal mengekspor')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        return null;
    }

    // ── Computed Properties ───────────────────────────────────────────────────

    #[Computed]
    public function metrics(): array
    {
        $from = $this->data['date_from'] ?? now()->startOfMonth()->toDateString();
        $to   = $this->data['date_to']   ?? now()->toDateString();

        $base = DB::table('orders')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        $totalRevenue = (clone $base)
            ->whereNotIn('status', [OrderStatus::CANCELLED->value])
            ->sum('total_amount');

        $prevFrom = now()->parse($from)->subMonth()->startOfMonth()->toDateString();
        $prevTo   = now()->parse($from)->subMonth()->endOfMonth()->toDateString();
        $prevRevenue = DB::table('orders')
            ->whereDate('created_at', '>=', $prevFrom)
            ->whereDate('created_at', '<=', $prevTo)
            ->whereNotIn('status', [OrderStatus::CANCELLED->value])
            ->sum('total_amount');

        $revenueChange = $prevRevenue > 0
            ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : null;

        return [
            'total_orders'     => (clone $base)->count(),
            'total_revenue'    => $totalRevenue,
            'revenue_change'   => $revenueChange,
            'done_orders'      => (clone $base)->where('status', OrderStatus::DONE->value)->count(),
            'cancelled_orders' => (clone $base)->where('status', OrderStatus::CANCELLED->value)->count(),
            'processing_orders'=> (clone $base)->where('status', OrderStatus::PROCESSING->value)->count(),
        ];
    }

    #[Computed]
    public function topProducts(): \Illuminate\Support\Collection
    {
        $from = $this->data['date_from'] ?? now()->startOfMonth()->toDateString();
        $to   = $this->data['date_to']   ?? now()->toDateString();

        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereDate('orders.created_at', '>=', $from)
            ->whereDate('orders.created_at', '<=', $to)
            ->whereNotIn('orders.status', [OrderStatus::CANCELLED->value])
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as order_count'),
            )
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function paymentMethodRecap(): \Illuminate\Support\Collection
    {
        $from = $this->data['date_from'] ?? now()->startOfMonth()->toDateString();
        $to   = $this->data['date_to']   ?? now()->toDateString();

        return DB::table('payment_confirmations')
            ->join('payment_methods', 'payment_methods.id', '=', 'payment_confirmations.payment_method_id')
            ->whereDate('payment_confirmations.created_at', '>=', $from)
            ->whereDate('payment_confirmations.created_at', '<=', $to)
            ->where('payment_confirmations.status', PaymentStatus::APPROVED->value)
            ->select(
                'payment_methods.name as method_name',
                'payment_methods.type as method_type',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(payment_confirmations.amount_paid) as total_amount'),
            )
            ->groupBy('payment_methods.id', 'payment_methods.name', 'payment_methods.type')
            ->orderByDesc('total_amount')
            ->get();
    }

    // ── Helper: format Rupiah ─────────────────────────────────────────────────

    public function formatRupiah(float|int|string $amount): string
    {
        return 'Rp ' . Number::format((float) $amount, 0, locale: 'id');
    }
}
