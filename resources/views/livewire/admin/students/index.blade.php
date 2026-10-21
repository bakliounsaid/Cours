<div>
    <x-ui.page-header :title="__('admin.nav.students')" :description="__('admin.students.description')" />

    <div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        <x-ui.stat-card :label="__('admin.students.stats.active')" :value="$stats['active']" icon="users" tone="navy" />
        <x-ui.stat-card :label="__('admin.students.stats.to_pay')" :value="$stats['to_pay']" icon="banknotes" tone="coral" />
        <x-ui.stat-card :label="__('admin.students.stats.month_payments')" :value="$stats['month_payments']" icon="chart" tone="green" class="col-span-2 lg:col-span-1" />
    </div>

    @include('tables.data-table')
</div>
