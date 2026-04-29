<?php
defined('BASEPATH') or exit('No direct script access allowed');

class View_ui_cont extends CI_Controller
{

    function __construct()
    {
        parent::__construct();

        date_default_timezone_set('Asia/Manila');
        // $this->db->query("SET time_zone = '+08:00'");

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        // Get Fish Statistics
        $data['fish_total_investment'] = $this->get_fish_total_investment();
        $data['fish_total_stock_value'] = $this->get_fish_total_stock_value();
        $data['fish_total_payments'] = $this->get_fish_total_payments();
        $data['fish_total_receivables'] = $this->get_fish_total_receivables();
        $data['fish_actual_profit'] = $this->get_fish_actual_profit();

        // Get Rice Statistics
        $data['rice_total_investment'] = $this->get_rice_total_investment();
        $data['rice_total_stock_value'] = $this->get_rice_total_stock_value();
        $data['rice_total_payments'] = $this->get_rice_total_payments();
        $data['rice_total_receivables'] = $this->get_rice_total_receivables();
        $data['rice_actual_profit'] = $this->get_rice_actual_profit();

        // Get other statistics
        $data['total_client'] = $this->db->count_all('tbl_client');
        $data['active_loans'] = $this->db->where('status', 'ongoing')->count_all_results('tbl_fish_transaction');
        $data['completed_loans'] = $this->db->where('status', 'completed')->count_all_results('tbl_fish_transaction');

        // ========== FIXED: SAFELY GET SELECTED DATE ==========
        $selected_date = $this->input->get('selected_date');
        $data['selected_date'] = !empty($selected_date) ? $selected_date : date('Y-m-d');

        // ========== ADD LOAN STATUS COUNTS ==========
        $today = date('Y-m-d');

        // Get fish loan counts
        $fish_ongoing = $this->db->where('status', 'ongoing')
            ->where('date_completed IS NULL', NULL, FALSE)
            ->count_all_results('tbl_fish_transaction');

        $fish_overdue = $this->db->where('status', 'ongoing')
            ->where('due_date <', $today)
            ->where('date_completed IS NULL', NULL, FALSE)
            ->count_all_results('tbl_fish_transaction');

        $fish_completed = $this->db->where('status', 'completed')
            ->count_all_results('tbl_fish_transaction');

        // Get rice loan counts
        $rice_ongoing = $this->db->where('status', 'ongoing')
            ->where('date_completed IS NULL', NULL, FALSE)
            ->count_all_results('tbl_rice_transaction');

        $rice_overdue = $this->db->where('status', 'ongoing')
            ->where('due_date <', $today)
            ->where('date_completed IS NULL', NULL, FALSE)
            ->count_all_results('tbl_rice_transaction');

        $rice_completed = $this->db->where('status', 'completed')
            ->count_all_results('tbl_rice_transaction');

        // Combine counts
        $data['loan_status_counts'] = [
            'ongoing' => $fish_ongoing + $rice_ongoing,
            'overdue' => $fish_overdue + $rice_overdue,
            'completed' => $fish_completed + $rice_completed
        ];

        // ========== ADD TODAY'S PAYMENTS ==========
        $this->db->select_sum('amt');
        $this->db->from('tbl_payment');
        $this->db->where('DATE(payment_for)', date('Y-m-d'));
        $today_query = $this->db->get();
        $data['today_payments'] = $today_query->row()->amt ?: 0;

        // Load views with data
        $this->load->view('layouts/header', $data);
        $this->load->view('dashboard', $data);
        $this->load->view('layouts/footer');
    }

    // ----FOR DAILY FILTERING
    public function get_payment_filter_data()
    {
        $selected_date = $this->input->get('selected_date');
        $range_type = $this->input->get('range_type');
        $loan_type = $this->input->get('loan_type'); // all, fish, rice

        if (!$selected_date) {
            $selected_date = date('Y-m-d');
        }

        if (!$range_type) {
            $range_type = 'day';
        }

        if (!$loan_type) {
            $loan_type = 'all';
        }

        // Calculate start and end dates based on range type
        switch ($range_type) {
            case 'day':
                $start_date = $selected_date;
                $end_date = $selected_date;
                break;
            case 'week':
                $start_date = date('Y-m-d', strtotime('monday this week', strtotime($selected_date)));
                $end_date = date('Y-m-d', strtotime('sunday this week', strtotime($selected_date)));
                break;
            case 'month':
                $start_date = date('Y-m-01', strtotime($selected_date));
                $end_date = date('Y-m-t', strtotime($selected_date));
                break;
            default:
                $start_date = $selected_date;
                $end_date = $selected_date;
        }

        $range_total = 0;

        // Get total payments based on loan type filter
        if ($loan_type == 'all' || $loan_type == 'fish') {
            $this->db->select_sum('amt')
                ->from('tbl_payment')
                ->where('payment_for >=', $start_date)
                ->where('payment_for <=', $end_date)
                ->where('type', 'fish');
            $query = $this->db->get();
            $range_total += $query->row()->amt ?: 0;
        }

        if ($loan_type == 'all' || $loan_type == 'rice') {
            $this->db->select_sum('amt')
                ->from('tbl_payment')
                ->where('payment_for >=', $start_date)
                ->where('payment_for <=', $end_date)
                ->where('type', 'rice');
            $query = $this->db->get();
            $range_total += $query->row()->amt ?: 0;
        }

        // Calculate days count
        $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;

        // Prepare response
        $response = [
            'success' => true,
            'data' => [
                'range_total' => $range_total,
                'range_total_formatted' => '₱' . number_format($range_total, 2),
                'start_date' => $start_date,
                'end_date' => $end_date,
                'start_date_display' => date('M j, Y', strtotime($start_date)),
                'end_date_display' => date('M j, Y', strtotime($end_date)),
                'selected_date' => $selected_date,
                'range_type' => $range_type,
                'loan_type' => $loan_type,
                'is_single_day' => ($range_type == 'day'),
                'days_count' => $days,
                'is_today' => ($range_type == 'day' && $selected_date == date('Y-m-d'))
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function get_loan_filter_data()
    {
        $selected_date = $this->input->get('selected_date');
        $range_type = $this->input->get('range_type');
        $loan_type = $this->input->get('loan_type');

        if (!$selected_date) {
            $selected_date = date('Y-m-d');
        }

        if (!$range_type) {
            $range_type = 'day';
        }

        if (!$loan_type) {
            $loan_type = 'all';
        }

        // Calculate start and end dates based on range type
        switch ($range_type) {
            case 'day':
                $start_date = $selected_date;
                $end_date = $selected_date;
                break;
            case 'week':
                $start_date = date('Y-m-d', strtotime('monday this week', strtotime($selected_date)));
                $end_date = date('Y-m-d', strtotime('sunday this week', strtotime($selected_date)));
                break;
            case 'month':
                $start_date = date('Y-m-01', strtotime($selected_date));
                $end_date = date('Y-m-t', strtotime($selected_date));
                break;
            default:
                $start_date = $selected_date;
                $end_date = $selected_date;
        }

        $range_total = 0;

        // Get total loan releases based on loan type filter
        if ($loan_type == 'all' || $loan_type == 'fish') {
            $this->db->select_sum('total_amt')
                ->from('tbl_fish_transaction')
                ->where('date_added >=', $start_date)
                ->where('date_added <=', $end_date);
            $query = $this->db->get();
            $range_total += $query->row()->total_amt ?: 0;
        }

        if ($loan_type == 'all' || $loan_type == 'rice') {
            $this->db->select_sum('total_amt')
                ->from('tbl_rice_transaction')
                ->where('date_added >=', $start_date)
                ->where('date_added <=', $end_date);
            $query = $this->db->get();
            $range_total += $query->row()->total_amt ?: 0;
        }

        // Calculate days count
        $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;

        // Prepare response
        $response = [
            'success' => true,
            'data' => [
                'range_total' => $range_total,
                'range_total_formatted' => '₱' . number_format($range_total, 2),
                'start_date' => $start_date,
                'end_date' => $end_date,
                'start_date_display' => date('M j, Y', strtotime($start_date)),
                'end_date_display' => date('M j, Y', strtotime($end_date)),
                'selected_date' => $selected_date,
                'range_type' => $range_type,
                'loan_type' => $loan_type,
                'is_single_day' => ($range_type == 'day'),
                'days_count' => $days,
                'is_today' => ($range_type == 'day' && $selected_date == date('Y-m-d'))
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function get_pullout_filter_data()
    {
        $selected_date = $this->input->get('selected_date');
        $range_type = $this->input->get('range_type');

        if (!$selected_date) {
            $selected_date = date('Y-m-d');
        }

        if (!$range_type) {
            $range_type = 'day';
        }

        // Calculate start and end dates based on range type
        switch ($range_type) {
            case 'day':
                $start_date = $selected_date;
                $end_date = $selected_date;
                break;
            case 'week':
                $start_date = date('Y-m-d', strtotime('monday this week', strtotime($selected_date)));
                $end_date = date('Y-m-d', strtotime('sunday this week', strtotime($selected_date)));
                break;
            case 'month':
                $start_date = date('Y-m-01', strtotime($selected_date));
                $end_date = date('Y-m-t', strtotime($selected_date));
                break;
            default:
                $start_date = $selected_date;
                $end_date = $selected_date;
        }

        // Get total payments for the date range
        $this->db->select_sum('total_pull_out')
            ->from('tbl_pull_out')
            ->where('date_added >=', $start_date)
            ->where('date_added <=', $end_date);

        $query = $this->db->get();
        $range_total = $query->row()->total_pull_out ?: 0;

        // Calculate days count
        $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;

        // Prepare response
        $response = [
            'success' => true,
            'data' => [
                'range_total' => $range_total,
                'range_total_formatted' => '₱' . number_format($range_total, 2),
                'start_date' => $start_date,
                'end_date' => $end_date,
                'start_date_display' => date('M j, Y', strtotime($start_date)),
                'end_date_display' => date('M j, Y', strtotime($end_date)),
                'selected_date' => $selected_date,
                'range_type' => $range_type,
                'is_single_day' => ($range_type == 'day'),
                'days_count' => $days,
                'is_today' => ($range_type == 'day' && $selected_date == date('Y-m-d'))
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }
    public function get_expenses_filter_data()
    {
        $selected_date = $this->input->get('selected_date');
        $range_type = $this->input->get('range_type');

        if (!$selected_date) {
            $selected_date = date('Y-m-d');
        }

        if (!$range_type) {
            $range_type = 'day';
        }

        // Calculate start and end dates based on range type
        switch ($range_type) {
            case 'day':
                $start_date = $selected_date;
                $end_date = $selected_date;
                break;
            case 'week':
                $start_date = date('Y-m-d', strtotime('monday this week', strtotime($selected_date)));
                $end_date = date('Y-m-d', strtotime('sunday this week', strtotime($selected_date)));
                break;
            case 'month':
                $start_date = date('Y-m-01', strtotime($selected_date));
                $end_date = date('Y-m-t', strtotime($selected_date));
                break;
            default:
                $start_date = $selected_date;
                $end_date = $selected_date;
        }

        // Get total payments for the date range
        $this->db->select_sum('amt')
            ->from('tbl_expenses')
            ->where('date_added >=', $start_date)
            ->where('date_added <=', $end_date);

        $query = $this->db->get();
        $range_total = $query->row()->amt ?: 0;

        // Calculate days count
        $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;

        // Prepare response
        $response = [
            'success' => true,
            'data' => [
                'range_total' => $range_total,
                'range_total_formatted' => '₱' . number_format($range_total, 2),
                'start_date' => $start_date,
                'end_date' => $end_date,
                'start_date_display' => date('M j, Y', strtotime($start_date)),
                'end_date_display' => date('M j, Y', strtotime($end_date)),
                'selected_date' => $selected_date,
                'range_type' => $range_type,
                'is_single_day' => ($range_type == 'day'),
                'days_count' => $days,
                'is_today' => ($range_type == 'day' && $selected_date == date('Y-m-d'))
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }
    // ----FOR DAILY FILTERING

    // ----FOR YEARLY FILTERING
    public function get_yearly_filter_data()
    {
        $year = $this->input->get('year');
        $data_type = $this->input->get('data_type');
        $loan_type = $this->input->get('loan_type');

        if (!$year) {
            $year = date('Y');
        }
        if (!$data_type) {
            $data_type = 'payments';
        }
        if (!$loan_type) {
            $loan_type = 'all';
        }

        $monthly_data = array_fill(0, 12, 0);
        $total = 0;

        // Get data based on type and loan filter
        for ($month = 1; $month <= 12; $month++) {
            $start_date = "$year-$month-01";
            $end_date = date('Y-m-t', strtotime($start_date));

            $amount = 0;

            switch ($data_type) {
                case 'payments':
                    if ($loan_type == 'all' || $loan_type == 'fish') {
                        $query = $this->db->select_sum('amt')
                            ->from('tbl_payment')
                            ->where('payment_for >=', $start_date)
                            ->where('payment_for <=', $end_date)
                            ->where('type', 'fish')
                            ->get();
                        $amount += $query->row()->amt ? (float) $query->row()->amt : 0;
                    }

                    if ($loan_type == 'all' || $loan_type == 'rice') {
                        $query = $this->db->select_sum('amt')
                            ->from('tbl_payment')
                            ->where('payment_for >=', $start_date)
                            ->where('payment_for <=', $end_date)
                            ->where('type', 'rice')
                            ->get();
                        $amount += $query->row()->amt ? (float) $query->row()->amt : 0;
                    }
                    break;

                case 'loan':
                    if ($loan_type == 'all' || $loan_type == 'fish') {
                        $query = $this->db->select_sum('total_amt')
                            ->from('tbl_fish_transaction')
                            ->where('date_added >=', $start_date)
                            ->where('date_added <=', $end_date)
                            ->get();
                        $amount += $query->row()->total_amt ? (float) $query->row()->total_amt : 0;
                    }

                    if ($loan_type == 'all' || $loan_type == 'rice') {
                        $query = $this->db->select_sum('total_amt')
                            ->from('tbl_rice_transaction')
                            ->where('date_added >=', $start_date)
                            ->where('date_added <=', $end_date)
                            ->get();
                        $amount += $query->row()->total_amt ? (float) $query->row()->total_amt : 0;
                    }
                    break;

                case 'pullout':
                    $query = $this->db->select_sum('total_pull_out')
                        ->from('tbl_pull_out')
                        ->where('date_added >=', $start_date)
                        ->where('date_added <=', $end_date)
                        ->get();
                    $amount = $query->row()->total_pull_out ? (float) $query->row()->total_pull_out : 0;
                    break;

                case 'expenses':
                    $query = $this->db->select_sum('amt')
                        ->from('tbl_expenses')
                        ->where('date_added >=', $start_date)
                        ->where('date_added <=', $end_date)
                        ->get();
                    $amount = $query->row()->amt ? (float) $query->row()->amt : 0;
                    break;
            }

            $monthly_data[$month - 1] = $amount;
            $total += $amount;
        }

        // Calculate highest and lowest months with error handling
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // Filter out zero values for finding lowest month
        $non_zero_data = array_filter($monthly_data);

        $highest_amount = !empty($monthly_data) ? max($monthly_data) : 0;
        $highest_index = $highest_amount > 0 ? array_search($highest_amount, $monthly_data) : 0;

        $lowest_amount = !empty($non_zero_data) ? min($non_zero_data) : 0;
        $lowest_index = $lowest_amount > 0 ? array_search($lowest_amount, $monthly_data) : 0;

        $response = [
            'success' => true,
            'data' => [
                'total' => (float) $total,
                'average' => $total > 0 ? (float) ($total / 12) : 0,
                'monthly_data' => array_map('floatval', $monthly_data),
                'highest' => [
                    'month' => $months[$highest_index],
                    'amount' => (float) $highest_amount
                ],
                'lowest' => [
                    'month' => $months[$lowest_index],
                    'amount' => (float) $lowest_amount
                ]
            ]
        ];

        echo json_encode($response);
    }
    // ----FOR YEARLY FILTERING

    // FISH STATISTICS METHODS
    private function get_fish_total_investment()
    {
        $result = $this->db->select("COALESCE(SUM(capital), 0) AS total")
            ->from('tbl_fish_stock')
            ->where('trans_type', 'in')
            ->get()
            ->row();
        return (float) $result->total;
    }

    private function get_fish_total_stock_value()
    {
        $sql = "
        SELECT COALESCE(
            SUM(CASE WHEN fs.trans_type = 'in' THEN (fs.qty/1000) * f.price_per_kg ELSE 0 END) - 
            SUM(CASE WHEN fs.trans_type = 'remove' THEN (fs.qty/1000) * f.price_per_kg ELSE 0 END), 0
        ) AS total
        FROM tbl_fish_stock fs
        JOIN tbl_fish f ON fs.fish_id = f.id
    ";
        $result = $this->db->query($sql)->row();
        return (float) $result->total;
    }

    private function get_fish_total_payments()
    {
        $result = $this->db->select("COALESCE(SUM(amt), 0) AS total")
            ->from('tbl_payment')
            ->where('type', 'fish')
            ->get()
            ->row();
        return (float) $result->total;
    }

    private function get_fish_total_receivables()
    {
        $sql = "
        SELECT COALESCE(SUM(
            ft.total_amt - COALESCE(p.paid_amount, 0)
        ), 0) AS total
        FROM tbl_fish_transaction ft
        LEFT JOIN (
            SELECT trans_id, SUM(amt) AS paid_amount
            FROM tbl_payment
            WHERE type = 'fish'
            GROUP BY trans_id
        ) p ON ft.id = p.trans_id
        WHERE ft.status = 'ongoing'
        AND ft.total_amt > COALESCE(p.paid_amount, 0)
    ";
        $result = $this->db->query($sql)->row();
        return max(0, (float) $result->total);
    }

    private function get_fish_actual_profit()
    {
        $total_payments = $this->get_fish_total_payments();
        $total_investment = $this->get_fish_total_investment();
        return $total_payments - $total_investment;
    }

    // RICE STATISTICS METHODS
    // RICE STATISTICS METHODS

    private function get_rice_total_investment()
    {
        $result = $this->db->select("COALESCE(SUM(capital), 0) AS total")
            ->from('tbl_rice_stock')
            ->where('trans_type', 'in')
            ->get()
            ->row();
        return (float) $result->total;
    }

    private function get_rice_total_stock_value()
    {
        // Join with tbl_rice to get sack_type and price_per_kg
        $sql = "
        SELECT COALESCE(SUM(
            CASE 
                WHEN rs.trans_type = 'in' THEN 
                    CASE 
                        WHEN r.sack_type = '25kg' THEN (rs.qty * 25) * r.price_per_kg
                        WHEN r.sack_type = '50kg' THEN (rs.qty * 50) * r.price_per_kg
                        ELSE 0
                    END
                ELSE 0 
            END - 
            CASE 
                WHEN rs.trans_type = 'remove' THEN 
                    CASE 
                        WHEN r.sack_type = '25kg' THEN (rs.qty * 25) * r.price_per_kg
                        WHEN r.sack_type = '50kg' THEN (rs.qty * 50) * r.price_per_kg
                        ELSE 0
                    END
                ELSE 0 
            END
        ), 0) AS total
        FROM tbl_rice_stock rs
        JOIN tbl_rice r ON rs.rice_id = r.id
    ";
        $result = $this->db->query($sql)->row();
        return (float) $result->total;
    }

    private function get_rice_total_payments()
    {
        $result = $this->db->select("COALESCE(SUM(amt), 0) AS total")
            ->from('tbl_payment')
            ->where('type', 'rice')
            ->get()
            ->row();
        return (float) $result->total;
    }

    private function get_rice_total_receivables()
    {
        $sql = "
        SELECT COALESCE(SUM(
            rt.total_amt - COALESCE(p.paid_amount, 0)
        ), 0) AS total
        FROM tbl_rice_transaction rt
        LEFT JOIN (
            SELECT trans_id, SUM(amt) AS paid_amount
            FROM tbl_payment
            WHERE type = 'rice'
            GROUP BY trans_id
        ) p ON rt.id = p.trans_id
        WHERE rt.status = 'ongoing'
        AND rt.total_amt > COALESCE(p.paid_amount, 0)
    ";
        $result = $this->db->query($sql)->row();
        return max(0, (float) $result->total);
    }

    private function get_rice_actual_profit()
    {
        $total_payments = $this->get_rice_total_payments();
        $total_investment = $this->get_rice_total_investment();
        return $total_payments - $total_investment;
    }

    public function get_years()
    {
        $this->db->select("DISTINCT YEAR(payment_for) as year")
            ->from('tbl_payment')
            ->order_by('year', 'DESC');

        $query = $this->db->get();
        $years = $query->result_array();

        $year_list = [];
        foreach ($years as $y) {
            if ($y['year']) {
                $year_list[] = $y['year'];
            }
        }

        // If no years in database, add current year
        if (empty($year_list)) {
            $year_list[] = date('Y');
        }

        echo json_encode($year_list);
    }

    public function masterfile()
    {
        $this->load->view('layouts/header');
        $this->load->view('masterfile');
        $this->load->view('layouts/footer');
    }

    public function product($id = null)
    {
        $data['product_id'] = $id;

        $this->load->view('layouts/header');
        $this->load->view('products', $data);
        $this->load->view('layouts/footer');
    }

    public function monitoring()
    {
        $this->load->view('layouts/header');
        $this->load->view('monitoring');
        $this->load->view('layouts/footer');
    }

    public function pull_out()
    {
        $this->load->view('layouts/header');
        $this->load->view('pull_out');
        $this->load->view('layouts/footer');
    }

    public function expenses()
    {
        $this->load->view('layouts/header');
        $this->load->view('expenses');
        $this->load->view('layouts/footer');
    }

    public function history()
    {
        $this->load->view('layouts/header');
        $this->load->view('history');
        $this->load->view('layouts/footer');
    }

}
