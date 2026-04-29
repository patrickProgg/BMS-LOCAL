<style>
    /* Professional Dashboard Styles */
    :root {
        --primary-blue: #2563eb;
        --primary-green: #059669;
        --primary-orange: #ea580c;
        --primary-purple: #7c3aed;
        --primary-red: #dc2626;
        --primary-teal: #0d9488;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
    }

    /* Dashboard Container */
    .dashboard-container {
        min-height: 100vh;
        padding: 24px 0;
    }

    /* Section Styles */
    .section-header {
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 10px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--gray-800);
        letter-spacing: -0.01em;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title i {
        font-size: 22px;
    }

    .section-badge {
        font-size: 12px;
        font-weight: 500;
        padding: 4px 10px;
        border-radius: 20px;
        background: var(--gray-100);
        color: var(--gray-600);
    }

    /* Grid Layout */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }

    /* Professional Cards */
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        transition: all 0.2s ease;
        border: 1px solid var(--gray-200);
        position: relative;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        border-color: transparent;
    }

    /* Card Accent Line */
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
    }

    /* Card Variations */
    .card-investment::before {
        background: var(--primary-orange);
    }

    .card-stock::before {
        background: var(--primary-teal);
    }

    .card-payment::before {
        background: var(--primary-purple);
    }

    .card-balance::before {
        background: var(--primary-blue);
    }

    .card-profit::before {
        background: var(--primary-green);
    }

    .card-loss::before {
        background: var(--primary-red);
    }

    /* Card Header Row - Icon next to label */
    .card-header-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }

    /* Card Icon - Small next to label */
    .card-icon-small {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .card-icon-small.investment {
        background: rgba(234, 88, 12, 0.1);
        color: var(--primary-orange);
    }

    .card-icon-small.stock {
        background: rgba(13, 148, 136, 0.1);
        color: var(--primary-teal);
    }

    .card-icon-small.payment {
        background: rgba(124, 58, 237, 0.1);
        color: var(--primary-purple);
    }

    .card-icon-small.balance {
        background: rgba(37, 99, 235, 0.1);
        color: var(--primary-blue);
    }

    .card-icon-small.profit {
        background: rgba(5, 150, 105, 0.1);
        color: var(--primary-green);
    }

    .card-icon-small.loss {
        background: rgba(220, 38, 38, 0.1);
        color: var(--primary-red);
    }

    .card-icon-small.client {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    /* Card Content */
    .card-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .card-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 4px;
        line-height: 1.2;
    }

    .card-value span {
        font-size: 14px;
        font-weight: 500;
    }

    .card-subtitle {
        font-size: 12px;
        color: var(--gray-600);
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .trend-indicator {
        font-size: 11px;
        font-weight: 500;
        padding: 2px 8px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .trend-up {
        background: rgba(5, 150, 105, 0.1);
        color: var(--primary-green);
    }

    .trend-down {
        background: rgba(220, 38, 38, 0.1);
        color: var(--primary-red);
    }

    /* Value Colors */
    .value-positive {
        color: var(--primary-green);
    }

    .value-negative {
        color: var(--primary-red);
    }

    /* Client Card */
    .client-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }

    .client-card .card-label,
    .client-card .card-subtitle {
        color: rgba(255, 255, 255, 0.8);
    }

    .client-card .card-value {
        color: white;
    }

    .client-card .card-icon-small {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }

    /* Summary Cards */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }

    .summary-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid var(--gray-200);
        transition: all 0.2s ease;
    }

    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }

    .summary-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .summary-title {
        font-size: 14px;
        font-weight: 500;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .summary-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .summary-value {
        font-size: 32px;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 8px;
    }

    .summary-value span {
        font-size: 14px;
        font-weight: 500;
    }

    .summary-footer {
        font-size: 12px;
        color: var(--gray-600);
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid var(--gray-200);
    }

    /* Responsive Breakpoints */
    @media (max-width: 1400px) {
        .stats-grid {
            gap: 15px;
        }

        .card-value {
            font-size: 24px;
        }

        .summary-value {
            font-size: 28px;
        }
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }

        .card-value {
            font-size: 28px;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 480px) {
        .stat-card {
            padding: 16px;
        }

        .card-value {
            font-size: 24px;
        }

        .summary-card {
            padding: 16px;
        }

        .summary-value {
            font-size: 24px;
        }
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-card,
    .summary-card {
        animation: fadeInUp 0.4s ease forwards;
    }

    .card {
        border: none;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
        border-radius: 10px;
        overflow: hidden;
    }

    .card-header {
        /* background: linear-gradient(135deg, #dbe2e7 0%, #339af0 100%); */
        background: var(--light-blue);
        color: white;
        border-bottom: none;
        padding: 20px 25px;
    }

    .card-header .card-title {
        font-weight: 600;
        font-size: 1.25rem;
        margin: 0;
    }

    .text-sm {
        font-size: 0.875rem;
    }

    .h4 {
        font-size: 1.5rem;
    }

    .bg-light {
        background-color: #f8f9fa !important;
        border: 1px solid #e9ecef;
    }

    .progress {
        border-radius: 4px;
        background-color: #e9ecef;
    }

    .progress-bar {
        border-radius: 4px;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .font-weight-bold {
        font-weight: 600 !important;
    }

    #yearSelect {
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
        font-weight: 500;
    }

    #yearSelect option {
        background-color: white;
        color: #495057;
    }

    #yearSelect:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
    }

    .bg-warning-light {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    .date-input-wrapper {
        position: relative;
    }

    .date-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        z-index: 10;
    }

    .date-input {
        padding-left: 40px;
        height: 48px;
        border-radius: 8px;
        border: 1px solid #ced4da;
        transition: all 0.3s;
        font-size: 16px;
    }

    .date-input:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
</style>

<div class="dashboard-container">
    <div class="container-fluid mb-1">

        <!-- FISH SECTION -->
        <div class="section-header">
            <div class="section-title">
                <i class='bx bx-fish' style="color: var(--primary-blue);"></i>
                <span>Dried Fish Analytics</span>
            </div>
            <div class="section-badge">
                <i class='bx bx-stats'></i> Real-time metrics
            </div>
        </div>

        <div class="stats-grid">
            <!-- Fish Investment -->
            <div class="stat-card card-investment">
                <div class="card-header-row">
                    <div class="card-icon-small investment">
                        <i class='bx bx-dollar-circle'></i>
                    </div>
                    <div class="card-label">Total Investment</div>
                </div>
                <div class="card-value">₱<?= number_format($fish_total_investment, 2) ?></div>
                <div class="card-subtitle">
                    <i class='bx bx-trending-up'></i>
                    <span>Capital allocated to fish stock</span>
                </div>
            </div>

            <!-- Fish Stock Value -->
            <div class="stat-card card-stock">
                <div class="card-header-row">
                    <div class="card-icon-small stock">
                        <i class='bx bx-package'></i>
                    </div>
                    <div class="card-label">Current Stock Value</div>
                </div>
                <div class="card-value">₱<?= number_format($fish_total_stock_value, 2) ?></div>
                <div class="card-subtitle">
                    <i class='bx bx-store'></i>
                    <span>Inventory at selling price</span>
                </div>
            </div>

            <!-- Fish Payments -->
            <div class="stat-card card-payment">
                <div class="card-header-row">
                    <div class="card-icon-small payment">
                        <i class='bx bx-wallet-alt'></i>
                    </div>
                    <div class="card-label">Total Collections</div>
                </div>
                <div class="card-value">₱<?= number_format($fish_total_payments, 2) ?></div>
                <div class="card-subtitle">
                    <i class='bx bx-check-circle'></i>
                    <span>Cash received from sales</span>
                </div>
            </div>

            <!-- Fish Balance -->
            <div class="stat-card card-balance">
                <div class="card-header-row">
                    <div class="card-icon-small balance">
                        <i class='bx bx-receipt'></i>
                    </div>
                    <div class="card-label">Outstanding Balance</div>
                </div>
                <div class="card-value">₱<?= number_format($fish_total_receivables, 2) ?></div>
                <div class="card-subtitle">
                    <i class='bx bx-time'></i>
                    <span>Pending customer payments</span>
                </div>
            </div>

            <!-- Fish Profit/Loss -->
            <div class="stat-card <?= $fish_actual_profit >= 0 ? 'card-profit' : 'card-loss' ?>">
                <div class="card-header-row">
                    <div class="card-icon-small <?= $fish_actual_profit >= 0 ? 'profit' : 'loss' ?>">
                        <i class='bx bx-trending-up'></i>
                    </div>
                    <div class="card-label">Realized P&L</div>
                </div>
                <div class="card-value <?= $fish_actual_profit >= 0 ? 'value-positive' : 'value-negative' ?>">
                    ₱<?= number_format(abs($fish_actual_profit), 2) ?>
                    <!-- <span><?= $fish_actual_profit >= 0 ? 'Profit' : 'Loss' ?></span> -->
                </div>
                <div class="card-subtitle">
                    <span class="trend-indicator <?= $fish_actual_profit >= 0 ? 'trend-up' : 'trend-down' ?>">
                        <i class='bx bx-bar-chart'></i>
                        <?= $fish_actual_profit >= 0 ? 'Operating Profit' : 'Operating Loss' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- RICE SECTION -->
        <div class="section-header" style="margin-top: 16px;">
            <div class="section-title">
                <i class='bx bx-seedling' style="color: var(--primary-green);"></i>
                <span>Rice Analytics</span>
            </div>
            <div class="section-badge">
                <i class='bx bx-stats'></i> Real-time metrics
            </div>
        </div>

        <div class="stats-grid">
            <!-- Rice Investment -->
            <div class="stat-card card-investment">
                <div class="card-header-row">
                    <div class="card-icon-small investment">
                        <i class='bx bx-dollar-circle'></i>
                    </div>
                    <div class="card-label">Total Investment</div>
                </div>
                <div class="card-value">₱<?= number_format($rice_total_investment, 2) ?></div>
                <div class="card-subtitle">
                    <i class='bx bx-trending-up'></i>
                    <span>Capital allocated to rice stock</span>
                </div>
            </div>

            <!-- Rice Stock Value -->
            <div class="stat-card card-stock">
                <div class="card-header-row">
                    <div class="card-icon-small stock">
                        <i class='bx bx-package'></i>
                    </div>
                    <div class="card-label">Current Stock Value</div>
                </div>
                <div class="card-value">₱<?= number_format($rice_total_stock_value, 2) ?></div>
                <div class="card-subtitle">
                    <i class='bx bx-store'></i>
                    <span>Inventory at selling price</span>
                </div>
            </div>

            <!-- Rice Payments -->
            <div class="stat-card card-payment">
                <div class="card-header-row">
                    <div class="card-icon-small payment">
                        <i class='bx bx-wallet-alt'></i>
                    </div>
                    <div class="card-label">Total Collections</div>
                </div>
                <div class="card-value">₱<?= number_format($rice_total_payments, 2) ?></div>
                <div class="card-subtitle">
                    <i class='bx bx-check-circle'></i>
                    <span>Cash received from sales</span>
                </div>
            </div>

            <!-- Rice Balance -->
            <div class="stat-card card-balance">
                <div class="card-header-row">
                    <div class="card-icon-small balance">
                        <i class='bx bx-receipt'></i>
                    </div>
                    <div class="card-label">Outstanding Balance</div>
                </div>
                <div class="card-value">₱<?= number_format($rice_total_receivables, 2) ?></div>
                <div class="card-subtitle">
                    <i class='bx bx-time'></i>
                    <span>Pending customer payments</span>
                </div>
            </div>

            <!-- Rice Profit/Loss -->
            <div class="stat-card <?= $rice_actual_profit >= 0 ? 'card-profit' : 'card-loss' ?>">
                <div class="card-header-row">
                    <div class="card-icon-small <?= $rice_actual_profit >= 0 ? 'profit' : 'loss' ?>">
                        <i class='bx bx-trending-up'></i>
                    </div>
                    <div class="card-label">Realized P&L</div>
                </div>
                <div class="card-value <?= $rice_actual_profit >= 0 ? 'value-positive' : 'value-negative' ?>">
                    ₱<?= number_format(abs($rice_actual_profit), 2) ?>
                    <!-- <span><?= $rice_actual_profit >= 0 ? 'Profit' : 'Loss' ?></span> -->
                </div>
                <div class="card-subtitle">
                    <span class="trend-indicator <?= $rice_actual_profit >= 0 ? 'trend-up' : 'trend-down' ?>">
                        <i class='bx bx-bar-chart'></i>
                        <?= $rice_actual_profit >= 0 ? 'Operating Profit' : 'Operating Loss' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- TOTAL ANALYTICS SECTION -->
        <div class="section-header" style="margin-top: 16px;">
            <div class="section-title">
                <i class='bx bx-stats' style="color: var(--primary-purple);"></i>
                <span>Total Analytics</span>
            </div>
            <div class="section-badge">
                <i class='bx bx-trending-up'></i> Combined metrics
            </div>
        </div>

        <!-- SUMMARY SECTION -->
        <div class="summary-grid">
            <!-- Total Assets Card -->
            <div class="summary-card">
                <div class="summary-header">
                    <span class="summary-title">Total Assets</span>
                    <div class="summary-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563eb;">
                        <i class='bx bx-building-house'></i>
                    </div>
                </div>
                <div class="summary-value">₱<?= number_format($fish_total_stock_value + $rice_total_stock_value, 2) ?>
                </div>
                <div class="summary-footer">
                    <i class='bx bx-trending-up'></i>
                    <span>Combined inventory value</span>
                </div>
            </div>

            <!-- Total Cash Flow Card -->
            <div class="summary-card">
                <div class="summary-header">
                    <span class="summary-title">Total Cash Flow</span>
                    <div class="summary-icon" style="background: rgba(5, 150, 105, 0.1); color: #059669;">
                        <i class='bx bx-credit-card'></i>
                    </div>
                </div>
                <div class="summary-value">₱<?= number_format($fish_total_payments + $rice_total_payments, 2) ?></div>
                <div class="summary-footer">
                    <i class='bx bx-wallet'></i>
                    <span>Total collections received</span>
                </div>
            </div>

            <!-- Total Receivables Card -->
            <div class="summary-card">
                <div class="summary-header">
                    <span class="summary-title">Total Receivables</span>
                    <div class="summary-icon" style="background: rgba(124, 58, 237, 0.1); color: #7c3aed;">
                        <i class='bx bx-receipt'></i>
                    </div>
                </div>
                <div class="summary-value">₱<?= number_format($fish_total_receivables + $rice_total_receivables, 2) ?>
                </div>
                <div class="summary-footer">
                    <i class='bx bx-time'></i>
                    <span>Outstanding customer balances</span>
                </div>
            </div>

            <!-- Net Position Card -->
            <div class="summary-card">
                <div class="summary-header">
                    <span class="summary-title">Net Financial Position</span>
                    <div class="summary-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class='bx bx-line-chart'></i>
                    </div>
                </div>
                <?php
                $net_position = ($fish_total_payments + $rice_total_payments) - ($fish_total_investment + $rice_total_investment);
                ?>
                <div class="summary-value <?= $net_position >= 0 ? 'value-positive' : 'value-negative' ?>">
                    ₱<?= number_format(abs($net_position), 2) ?>
                    <span><?= $net_position >= 0 ? 'Net Profit' : 'Net Loss' ?></span>
                </div>
                <div class="summary-footer">
                    <i class='bx bx-chart'></i>
                    <span>Overall business performance</span>
                </div>
            </div>
        </div>

        <!-- CLIENT SECTION -->
        <div class="section-header" style="margin-top: 16px;">
            <div class="section-title">
                <i class='bx bx-group' style="color: #3b82f6;"></i>
                <span>Client Relations</span>
            </div>
            <div class="section-badge">
                <i class='bx bx-user-plus'></i> Active accounts
            </div>
        </div>

        <div class="stats-grid mb-0">
            <a href="<?= base_url(); ?>client" style="text-decoration: none; display: block;">
                <div class="stat-card client-card">
                    <div class="card-header-row">
                        <div class="card-icon-small client">
                            <i class='bx bx-group'></i>
                        </div>
                        <div class="card-label" style="color: rgba(255,255,255,0.7);">Total Registered</div>
                    </div>
                    <div class="card-value" style="color: white;"><?php echo number_format($total_client); ?></div>
                    <div class="card-subtitle" style="color: rgba(255,255,255,0.6);">
                        <i class='bx bx-user-check'></i>
                        <span>Active client accounts</span>
                        <i class='bx bx-chevron-right' style="margin-left: auto;"></i>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>

<div class="row mb-3">
    <div class="col-md-8 px-3">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0" style="display: flex; align-items: center; gap: 10px;">
                        <span>💰 Daily</span>
                        <div style="display: flex; gap: 5px;">

                            <select id="dailyTypeSelect" class="form-control-sm border-info"
                                style="width: 120px; display: inline-block; height: 28px; color: #444242; border-radius: 6px;">
                                <option value="payments">Payments</option>
                                <option value="loan">Total Credits</option>
                                <option value="pullout">Pull Out</option>
                                <option value="expenses">Expenses</option>
                            </select>

                            <!-- NEW DROPDOWN FOR LOAN TYPE -->
                            <select id="loanTypeFilter" class="form-control-sm border-info"
                                style="width: 100px; display: inline-block; height: 28px; color: #444242; border-radius: 6px;">
                                <option value="all">All</option>
                                <option value="fish">Dried Fish</option>
                                <option value="rice">Rice</option>
                            </select>

                            <input type="date"
                                style="font-size: 12px; width: 150px; display: inline-block; height: 28px; background-color: white; color: #444242; border-radius: 6px; border:1px solid var(--bs-info)"
                                class="form-control" id="selected_date" name="selected_date"
                                value="<?php echo $selected_date; ?>">
                        </div>
                    </h3>
                </div>
            </div>
            <div class="card-body">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h4 class="text-muted mb-3" id="dateRangeText">
                            <!-- Will be updated via AJAX -->
                        </h4>

                        <div class="display-4 font-weight-bold text-success mb-3" id="rangeTotalDisplay">
                            <!-- Will be updated via AJAX -->
                        </div>

                        <div class="text-muted" id="rangeInfoDisplay">
                            <!-- Will be updated via AJAX -->
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-center">
                    <small class="text-muted">
                        Quick select:
                        <a href="#" data-range="day" data-date="<?php echo date('Y-m-d'); ?>"
                            class="btn btn-sm btn-outline-secondary quick-select">
                            Today
                        </a>
                        <a href="#" data-range="week" data-date="<?php echo $selected_date; ?>"
                            class="btn btn-sm btn-outline-secondary quick-select">
                            Week
                        </a>
                        <a href="#" data-range="month" data-date="<?php echo $selected_date; ?>"
                            class="btn btn-sm btn-outline-secondary quick-select">
                            Month
                        </a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 px-3">
        <div class="row h-100">
            <div class="col-md-12 d-flex">
                <div class="card border-left-success shadow h-100 w-100">
                    <div class="card-body py-2 d-flex flex-column justify-content-between">
                        <div class="row no-gutters align-items-center flex-grow-1">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Ongoing Loans
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $loan_status_counts['ongoing'] ?? 0; ?>
                                </div>
                                <div class="mt-2">
                                    <span class="badge badge-primary text-primary">Active</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-sync-alt fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 d-flex mt-3">
                <div class="card border-left-warning shadow h-100 w-100">
                    <div class="card-body py-2 d-flex flex-column justify-content-between">
                        <div class="row no-gutters align-items-center flex-grow-1">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Overdue Loans
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $loan_status_counts['overdue'] ?? 0; ?>
                                </div>
                                <div class="mt-2">
                                    <span class="badge badge-danger text-danger">Attention Needed</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 d-flex mt-3">
                <div class="card border-left-info shadow h-100 w-100">
                    <div class="card-body py-2 d-flex flex-column justify-content-between">
                        <div class="row no-gutters align-items-center flex-grow-1">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Completed Loans
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $loan_status_counts['completed'] ?? 0; ?>
                                </div>
                                <div class="mt-2">
                                    <span class="badge badge-success text-success">Paid Off</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 px-3">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <span class="mr-2">📊 Yearly</span>
                        <select id="dataTypeSelect" class="form-control-sm border-info"
                            style="width: 120px; display: inline-block; height: 28px; color: #444242; border-radius: 6px;">
                            <option value="payments">Payments</option>
                            <option value="loan">Total Credits</option>
                            <option value="pullout">Pull Out</option>
                            <option value="expenses">Expenses</option>
                        </select>
                        <select id="yearSelect" class="form-control-sm border-info"
                            style="width: 80px; display: inline-block; height: 28px; background-color: white; color: #444242;">
                        </select>
                        <select id="yearLoanTypeFilter" class="form-control-sm border-info"
                            style="width: 100px; display: inline-block; height: 28px; color: #444242; border-radius: 6px;">
                            <option value="all">All</option>
                            <option value="fish">Dried Fish</option>
                            <option value="rice">Rice</option>
                        </select>
                    </h3>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <canvas id="paymentChart"
                            style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title text-center mb-4">💰 Year <span id="yearDisplay"></span> Summary
                                </h5>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Total</span>
                                        <span class="font-weight-bold text-success" id="yearTotal">₱0.00</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: 100%"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Average Monthly</span>
                                        <span class="font-weight-bold text-primary" id="yearAverage">₱0.00</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" id="avgProgressBar" style="width: 0%">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Highest Month (<span
                                                id="highestMonthName">N/A</span>)</span>
                                        <span class="font-weight-bold text-warning" id="highestMonthAmount">₱0.00</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-warning" id="highestProgressBar" style="width: 0%">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Lowest Month (<span
                                                id="lowestMonthName">N/A</span>)</span>
                                        <span class="font-weight-bold text-info" id="lowestMonthAmount">₱0.00</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-info" id="lowestProgressBar" style="width: 0%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Track current values
        let currentDate = $('#selected_date').val();
        let currentRangeType = getCurrentRangeType();
        let currentDailyType = $('#dailyTypeSelect').val();
        let currentLoanType = $('#loanTypeFilter').val();

        // Handle date input change
        $('#selected_date').change(function () {
            currentDate = $(this).val();
            currentRangeType = getCurrentRangeType();
            currentDailyType = $('#dailyTypeSelect').val();
            currentLoanType = $('#loanTypeFilter').val();
            loadPaymentFilterData();
        });

        $('#loanTypeFilter').change(function () {
            currentLoanType = $(this).val();
            loadPaymentFilterData();
        });

        // Handle daily type select change
        $('#dailyTypeSelect').change(function () {
            currentDailyType = $(this).val();
            currentDate = $('#selected_date').val();
            currentRangeType = getCurrentRangeType();
            currentLoanType = $('#loanTypeFilter').val();
            loadPaymentFilterData();
        });

        // Handle quick select clicks
        $('.quick-select').click(function (e) {
            e.preventDefault();

            // Update active state
            $('.quick-select').removeClass('active btn-secondary').addClass('btn-outline-secondary');
            $(this).removeClass('btn-outline-secondary').addClass('active btn-secondary');

            // Update current values
            currentRangeType = $(this).data('range');
            currentDate = $(this).data('date');
            currentDailyType = $('#dailyTypeSelect').val();
            currentLoanType = $('#loanTypeFilter').val();

            // Update date input
            $('#selected_date').val(currentDate);

            // Load data
            loadPaymentFilterData();
        });

        // Main function to load payment filter data
        function loadPaymentFilterData() {
            let url = getFilterDataUrl(currentDailyType);
            let loanType = currentLoanType;

            console.log('Loading data for:', {
                url: url,
                date: currentDate,
                range: currentRangeType,
                loanType: loanType,
                dailyType: currentDailyType
            });

            $('#rangeTotalDisplay').html('<i class="fas fa-spinner fa-spin"></i>');
            $('#dateRangeText').html('Loading...');

            $.ajax({
                url: url,
                method: 'GET',
                data: {
                    selected_date: currentDate,
                    range_type: currentRangeType,
                    loan_type: loanType
                },
                dataType: 'json',
                success: function (response) {
                    console.log('Response:', response);
                    if (response.success) {
                        updatePaymentFilterUI(response.data);
                        $('#dailyTypeSelect').val(currentDailyType);
                    } else {
                        console.error('Error in response:', response);
                        showErrorState();
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', error);
                    showErrorState();
                }
            });
        }

        // Helper function to get URL based on daily type
        function getFilterDataUrl(dailyType) {
            const urls = {
                'payments': '<?php echo site_url("View_ui_cont/get_payment_filter_data"); ?>',
                'loan': '<?php echo site_url("View_ui_cont/get_loan_filter_data"); ?>',
                'pullout': '<?php echo site_url("View_ui_cont/get_pullout_filter_data"); ?>',
                'expenses': '<?php echo site_url("View_ui_cont/get_expenses_filter_data"); ?>'
            };
            return urls[dailyType] || '<?php echo site_url("View_ui_cont/get_payment_filter_data"); ?>';
        }

        // Function to update the UI
        function updatePaymentFilterUI(data) {
            const typeLabel = getDataTypeLabel(currentDailyType);
            let dateRangeHtml = '';

            if (data.is_single_day) {
                dateRangeHtml = `${typeLabel} for <span class="text-primary">${data.start_date_display}</span>`;
            } else {
                dateRangeHtml = `${typeLabel} from <span class="text-primary">${data.start_date_display}</span> to <span class="text-primary">${data.end_date_display}</span>`;
            }
            $('#dateRangeText').html(dateRangeHtml);

            const totalColor = getTotalColorClass(currentDailyType);
            $('#rangeTotalDisplay')
                .removeClass('text-success text-danger text-info text-warning')
                .addClass(totalColor)
                .text(data.range_total_formatted);

            let rangeInfoHtml = `<i class="fas fa-calendar-alt"></i> `;
            if (data.is_single_day) {
                rangeInfoHtml += 'Single day';
            } else {
                rangeInfoHtml += data.days_count + ' day' + (data.days_count > 1 ? 's' : '');
            }

            if (data.is_today) {
                rangeInfoHtml += ` <span class="badge badge-success ml-2 text-muted">Today</span>`;
            }

            $('#rangeInfoDisplay').html(rangeInfoHtml);
        }

        function getDataTypeLabel(dataType) {
            const labels = {
                'payments': 'Payments',
                'loan': 'Loan Releases',
                'pullout': 'Pull Outs',
                'expenses': 'Expenses'
            };
            return labels[dataType] || 'Transactions';
        }

        function getTotalColorClass(dataType) {
            const colors = {
                'payments': 'text-success',
                'loan': 'text-info',
                'pullout': 'text-warning',
                'expenses': 'text-danger'
            };
            return colors[dataType] || 'text-success';
        }

        function showErrorState() {
            $('#rangeTotalDisplay').text('Error loading data');
            $('#dateRangeText').text('Failed to load data');
            $('#rangeInfoDisplay').html('<i class="fas fa-exclamation-triangle text-danger"></i> Please try again');
        }

        function getCurrentRangeType() {
            const activeButton = $('.quick-select.active');
            if (activeButton.length) {
                return activeButton.data('range');
            }
            return 'day';
        }

        // LOAD DATA ON PAGE READY - THIS IS THE KEY FIX
        loadPaymentFilterData();


        // -----------------YEARLY-------------------------------

        // Yearly chart variables
        let yearlyChart = null;
        let currentYear = new Date().getFullYear();
        let currentDataType = 'payments';
        let currentYearLoanType = 'all';

        // Populate year select dropdown
        function populateYearSelect() {
            const currentYear = new Date().getFullYear();
            const yearSelect = $('#yearSelect');
            yearSelect.empty();

            for (let year = currentYear; year >= 2020; year--) {
                yearSelect.append(`<option value="${year}">${year}</option>`);
            }
            yearSelect.val(currentYear);
        }

        // Load yearly data
        function loadYearlyData() {
            const year = $('#yearSelect').val();
            const dataType = $('#dataTypeSelect').val();
            const loanType = $('#yearLoanTypeFilter').val();

            $('#yearDisplay').text(year);

            $.ajax({
                url: '<?php echo site_url("View_ui_cont/get_yearly_filter_data"); ?>',
                method: 'GET',
                data: {
                    year: year,
                    data_type: dataType,
                    loan_type: loanType
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        updateYearlyUI(response.data);
                        updateYearlyChart(response.data);
                    } else {
                        console.error('Error loading yearly data:', response);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }

        // Update yearly UI
        function updateYearlyUI(data) {
            const total = data.total || 0;
            const average = data.average || 0;
            const highest = data.highest || { month: 'N/A', amount: 0 };
            const lowest = data.lowest || { month: 'N/A', amount: 0 };

            $('#yearTotal').text(`₱${total.toLocaleString('en-US', { minimumFractionDigits: 2 })}`);
            $('#yearAverage').text(`₱${average.toLocaleString('en-US', { minimumFractionDigits: 2 })}`);

            $('#highestMonthName').text(highest.month);
            $('#highestMonthAmount').text(`₱${highest.amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`);

            $('#lowestMonthName').text(lowest.month);
            $('#lowestMonthAmount').text(`₱${lowest.amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`);

            // Update progress bars
            const maxAmount = Math.max(total, highest.amount, 1);
            $('#avgProgressBar').css('width', `${(average / maxAmount) * 100}%`);
            $('#highestProgressBar').css('width', `${(highest.amount / maxAmount) * 100}%`);
            $('#lowestProgressBar').css('width', `${(lowest.amount / maxAmount) * 100}%`);
        }

        // Update yearly chart
        function updateYearlyChart(data) {
            const ctx = document.getElementById('paymentChart').getContext('2d');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            if (yearlyChart) {
                yearlyChart.destroy();
            }

            yearlyChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: getChartLabel($('#dataTypeSelect').val()),
                        data: data.monthly_data || Array(12).fill(0),
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        // y: {
                        //     beginAtZero: true,
                        //     ticks: {
                        //         callback: function (value) {
                        //             return '₱' + value.toLocaleString();
                        //         }
                        //     }
                        // }

                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#6c757d',
                                callback: function (value) {
                                    return '₱' + value.toLocaleString();
                                }
                            },
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#495057',
                                font: {
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return '₱' + context.raw.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        $.ajax({
            url: '<?php echo site_url("View_ui_cont/get_years"); ?>',
            method: 'GET',
            dataType: 'json',
            success: function (years) {
                const yearSelect = $('#yearSelect');
                yearSelect.empty();

                years.forEach(function (year) {
                    const selected = year == currentYear ? 'selected' : '';
                    yearSelect.append(`<option value="${year}" ${selected}>${year}</option>`);
                });
            }
        });

        function getChartLabel(dataType) {
            const labels = {
                'payments': 'Payments',
                'loan': 'Loan Releases',
                'pullout': 'Pull Outs',
                'expenses': 'Expenses'
            };
            return labels[dataType] || 'Amount';
        }

        // Event handlers for yearly section
        $(document).ready(function () {
            // populateYearSelect();
            loadYearlyData();

            $('#yearSelect').change(function () {
                loadYearlyData();
            });

            $('#dataTypeSelect').change(function () {
                loadYearlyData();
            });

            $('#yearLoanTypeFilter').change(function () {
                loadYearlyData();
            });
        });
        // -----------------YEARLY-------------------------------
    });
</script>