<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . '../vendor/autoload.php'; // adjust path if needed

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class Monitoring_cont extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    public function get_client()
    {
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $searchValue = trim($this->input->post('search')['value']);

        $order = $this->input->post('order');
        $orderColumnIndex = isset($order[0]['column']) ? $order[0]['column'] : 0;
        $orderDir = isset($order[0]['dir']) ? $order[0]['dir'] : 'DESC';

        $columns = [
            0 => 'acc_no',
            1 => 'full_name',
            2 => 'address',
            3 => 'date_added'
        ];

        $orderColumn = $columns[$orderColumnIndex];

        $this->db->select('
            id,
            acc_no,
            full_name,
            address,
            date_added
        ');

        $this->db->from('tbl_client');
        $this->db->where('status', '0');

        $this->db->group_by('id');

        if (!empty($searchValue)) {
            $this->db->group_start();
            $this->db->like('full_name', $searchValue);
            $this->db->or_like('address', $searchValue);
            $this->db->or_like('date_added', $searchValue);
            $this->db->or_like('id', $searchValue);
            $this->db->group_end();
        }

        $this->db->group_by('id');

        $this->db->order_by($orderColumn, $orderDir);

        $subQuery = clone $this->db;
        $recordsFiltered = $subQuery->get()->num_rows();

        $this->db->limit($length, $start);

        $query = $this->db->get();
        $data = $query->result_array();

        echo json_encode([
            "draw" => intval($this->input->post('draw')),
            "recordsTotal" => $recordsFiltered,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }

    public function add_client()
    {
        $acc_no = $this->input->post('acc_no');
        $full_name_input = $this->input->post('full_name');

        $normalized_full_name = strtolower(str_replace([',', ' '], '', $full_name_input));

        $exists = $this->db
            ->where('acc_no', $acc_no)
            ->or_where("REPLACE(REPLACE(LOWER(full_name), ',', ''), ' ', '') = ", $normalized_full_name)
            ->get('tbl_client')
            ->row();

        if ($exists) {
            $message = '';

            $db_full_name_normalized = strtolower(str_replace([',', ' '], '', $exists->full_name));

            if ($exists->acc_no == $acc_no && $db_full_name_normalized == $normalized_full_name) {
                $message = 'Account number and full name already exist.';
            } elseif ($exists->acc_no == $acc_no) {
                $message = 'Account number already exists.';
            } elseif ($db_full_name_normalized == $normalized_full_name) {
                $message = 'Client with this full name already exists.';
            }

            echo json_encode([
                'status' => 'exist',
                'message' => $message
            ]);
            return;
        }

        $client_details = [
            'acc_no' => $acc_no,
            'full_name' => $this->input->post('full_name'),
            'address' => $this->input->post('address'),
            'date_added' => $this->input->post('date_added'),
        ];

        $inserted = $this->db->insert('tbl_client', $client_details);

        if (!$inserted) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to save client.'
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Client saved successfully.'
            ]);
        }
    }

    public function update_client()
    {
        $id = $this->input->post('id');

        $client_details = array(
            'acc_no' => $this->input->post('edit_acc_no'),
            'full_name' => $this->input->post('edit_full_name'),
            'address' => $this->input->post('edit_address'),
            'date_added' => $this->input->post('edit_start_date'),
        );

        $this->db->where('id', $id);
        $updated = $this->db->update('tbl_client', $client_details);

        if (!$updated) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update client details.'
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Client updated successfully.'
            ]);
        }
    }

    public function get_fish_types()
    {
        $this->db->select("
        f.id, 
        f.fish_type, 
        f.price_per_kg,
        ROUND(
            (COALESCE(SUM(CASE WHEN fs.trans_type = 'in' THEN fs.qty ELSE 0 END), 0) - 
             COALESCE(SUM(CASE WHEN fs.trans_type = 'out' THEN fs.qty ELSE 0 END), 0) - 
             COALESCE(SUM(CASE WHEN fs.trans_type = 'remove' THEN fs.qty ELSE 0 END), 0)) / 1000, 
        3) as rem_qty,
        COALESCE(SUM(CASE WHEN fs.trans_type = 'in' THEN fs.qty ELSE 0 END), 0) / 1000 as total_in,
        COALESCE(SUM(CASE WHEN fs.trans_type = 'out' THEN fs.qty ELSE 0 END), 0) / 1000 as total_out
    ");

        $this->db->from('tbl_fish f');
        $this->db->join('tbl_fish_stock fs', 'f.id = fs.fish_id', 'left');
        $this->db->group_by('f.id, f.fish_type, f.price_per_kg');
        $this->db->order_by('f.fish_type', 'ASC');

        $query = $this->db->get();
        echo json_encode($query->result_array());
    }

    public function get_rice_types()
    {
        $this->db->select("
        r.id, 
        r.rice_type, 
        r.sack_type,
        r.price_per_kg,
        ROUND(
            (COALESCE(SUM(CASE WHEN rs.trans_type = 'in' THEN rs.qty ELSE 0 END), 0) - 
             COALESCE(SUM(CASE WHEN rs.trans_type = 'out' THEN rs.qty ELSE 0 END), 0) - 
             COALESCE(SUM(CASE WHEN rs.trans_type = 'remove' THEN rs.qty ELSE 0 END), 0)), 
        0) as rem_qty,
        COALESCE(SUM(CASE WHEN rs.trans_type = 'in' THEN rs.qty ELSE 0 END), 0) as total_in,
        COALESCE(SUM(CASE WHEN rs.trans_type = 'out' THEN rs.qty ELSE 0 END), 0) as total_out,
        COALESCE(SUM(CASE WHEN rs.trans_type = 'remove' THEN rs.qty ELSE 0 END), 0) as total_remove
    ");

        $this->db->from('tbl_rice r');
        $this->db->join('tbl_rice_stock rs', 'r.id = rs.rice_id', 'left');
        $this->db->group_by('r.id, r.rice_type, r.sack_type, r.price_per_kg');
        $this->db->order_by('r.rice_type', 'ASC');

        $query = $this->db->get();

        // Format the response
        $result = $query->result_array();
        foreach ($result as &$row) {
            $row['rem_qty'] = $row['rem_qty'] ? intval($row['rem_qty']) : 0;
            $row['total_in'] = $row['total_in'] ? intval($row['total_in']) : 0;
            $row['total_out'] = $row['total_out'] ? intval($row['total_out']) : 0;
            $row['total_remove'] = $row['total_remove'] ? intval($row['total_remove']) : 0;
        }

        echo json_encode($result);
    }

    public function get_start_due_date()
    {
        $id = $this->input->post('id');

        // Fetch fish transactions
        $this->db->select("
            id,
            CONCAT(date_added, ' - ', due_date) AS date_to_pay,
            status,
            'fish' as type
        ");
        $this->db->from('tbl_fish_transaction');
        $this->db->where('cl_id', $id);
        $fish_query = $this->db->get();
        $fish_data = $fish_query->result_array();

        // Fetch rice transactions
        $this->db->select("
            id,
            CONCAT(date_added, ' - ', due_date) AS date_to_pay,
            status,
            'rice' as type
        ");
        $this->db->from('tbl_rice_transaction');
        $this->db->where('cl_id', $id);
        $rice_query = $this->db->get();
        $rice_data = $rice_query->result_array();

        $all_data = array_merge($fish_data, $rice_data);

        echo json_encode([$all_data]);
    }

    // public function get_loan_details()
    // {
    //     $id = $this->input->post('id');

    //     $this->db->select("
    //     a.capital_amt,
    //     a.interest,
    //     a.added_amt,
    //     a.total_amt,
    //     a.start_date,
    //     a.due_date,
    //     a.complete_date,
    //     a.status,
    //     b.payment_for,
    //     b.amt
    // ");

    //     $this->db->from('tbl_loan as a');
    //     $this->db->join('tbl_payment as b', 'b.loan_id = a.id', 'left');
    //     $this->db->where('a.id', $id);
    //     $this->db->order_by('b.payment_for', 'ASC');

    //     $query = $this->db->get()->result_array();

    //     $key = "12345678901234567890123456789012"; // 32 chars
    //     $iv = "1234567890123456"; // 16 chars

    //     $encrypted = openssl_encrypt(
    //         json_encode($query),
    //         'AES-256-CBC',
    //         $key,
    //         0,
    //         $iv
    //     );

    //     echo json_encode(['data' => $encrypted]);
    // }


    public function get_loan_details()
    {
        $id = $this->input->post('id'); // trans_id
        $type = $this->input->post('type'); // fish or rice

        if ($type === 'fish') {
            // Fetch fish transaction header
            $this->db->select("
                ft.cl_id,
                ft.id as trans_id,
                ft.total_amt,
                ft.status,
                ft.date_added as start_date,
                ft.due_date,
                ft.date_completed
            ");
            $this->db->from('tbl_fish_transaction ft');
            $this->db->where('ft.id', $id);
            $query = $this->db->get();
            $data = $query->row_array();

            // Fetch fish transaction details
            $this->db->select("
                f.fish_type,
                ftd.qty,
                ftd.price_per_kg,
                ftd.interest,
                ftd.added_amt,
                ftd.sub_total
            ");
            $this->db->from('tbl_fish_transaction_details ftd');
            $this->db->join('tbl_fish f', 'f.id = ftd.fish_id');
            $this->db->where('ftd.trans_id', $id);
            $details_query = $this->db->get();
            $details = $details_query->result_array();

            // Fetch payments for this trans_id
            $this->db->select('payment_for, amt');
            $this->db->from('tbl_payment');
            $this->db->where('trans_id', $id);
            $this->db->where('type', $type);
            $payment_query = $this->db->get();
            $payments = $payment_query->result_array();

            echo json_encode([
                'type' => 'fish',
                'data' => $data,
                'details' => $details,
                'payments' => $payments
            ]);
        } elseif ($type === 'rice') {
            // Fetch rice transaction header
            $this->db->select("
                rt.cl_id,
                rt.id as trans_id,
                rt.total_amt,
                rt.status,
                rt.date_added as start_date,
                rt.due_date,
                rt.date_completed
            ");
            $this->db->from('tbl_rice_transaction rt');
            $this->db->where('rt.id', $id);
            $query = $this->db->get();
            $data = $query->row_array();

            // Fetch rice transaction detail rows
            $this->db->select("
                r.rice_type,
                r.sack_type,
                rtd.qty,
                rtd.price_per_kg,
                rtd.interest,
                rtd.added_amt,
                rtd.sub_total
            ");
            $this->db->from('tbl_rice_transaction_details rtd');
            $this->db->join('tbl_rice r', 'r.id = rtd.rice_id');
            $this->db->where('rtd.trans_id', $id);
            $details_query = $this->db->get();
            $details = $details_query->result_array();

            // Fetch payments
            $this->db->select('payment_for, amt');
            $this->db->from('tbl_payment');
            $this->db->where('trans_id', $id);
            $this->db->where('type', $type);
            $payment_query = $this->db->get();
            $payments = $payment_query->result_array();

            echo json_encode([
                'type' => 'rice',
                'data' => $data,
                'details' => $details,
                'payments' => $payments
            ]);
        }
    }

    public function save_payment()
    {
        $trans_id = $this->input->post('trans_id');
        $payment_for = $this->input->post('payment_for');
        $amount = $this->input->post('amount');
        $type = $this->input->post('type');

        $this->db->where('trans_id', $trans_id);
        $this->db->where('type', $type);
        $this->db->where('payment_for', $payment_for);
        $query = $this->db->get('tbl_payment');

        if ($query->num_rows() > 0) {
            $this->db->set('amt', $amount);
            $this->db->where('trans_id', $trans_id);
            $this->db->where('type', $type);
            $this->db->where('payment_for', $payment_for);
            $updated = $this->db->update('tbl_payment');

            if ($updated) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Payment updated successfully.'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update payment.'
                ]);
            }
        } else {
            $payment_details = array(
                'trans_id' => $trans_id,
                'payment_for' => $payment_for,
                'amt' => $amount,
                'type' => $type,
                'date_added' => date('Y-m-d')
            );

            $inserted = $this->db->insert('tbl_payment', $payment_details);

            if ($inserted) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Payment inserted successfully.'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to insert payment.'
                ]);
            }
        }
    }

    public function get_all_loans_for_client()
    {
        $client_id = $this->input->post('client_id');

        // Get all fish transactions for the client
        $this->db->select("
            ft.id as trans_id,
            ft.total_amt,
            ft.status,
            ft.date_added as start_date,
            ft.due_date,
            'fish' as type
        ");
        $this->db->from('tbl_fish_transaction ft');
        $this->db->where('ft.cl_id', $client_id);
        $this->db->order_by('ft.date_added', 'DESC');
        $fish_query = $this->db->get();
        $fish_loans = $fish_query->result_array();

        // Get all rice transactions for the client
        $this->db->select("
            rt.id as trans_id,
            rt.total_amt,
            rt.status,
            rt.date_added as start_date,
            rt.due_date,
            'rice' as type
        ");
        $this->db->from('tbl_rice_transaction rt');
        $this->db->where('rt.cl_id', $client_id);
        $this->db->order_by('rt.date_added', 'DESC');
        $rice_query = $this->db->get();
        $rice_loans = $rice_query->result_array();

        echo json_encode([
            'fish_loans' => $fish_loans,
            'rice_loans' => $rice_loans
        ]);
    }

    public function complete_payment()
    {
        $loan_id = $this->input->post('loan_id');
        $complete_date = $this->input->post('complete_date');
        $action = $this->input->post('action');
        $running_bal = $this->input->post('running_bal');
        $due_date = $this->input->post('due_date');
        $new_start_date = $this->input->post('new_start_date');
        $interest = $this->input->post('interest');
        $added_amt = $this->input->post('added_amt');
        $total_amt = $this->input->post('total_amt');
        $type = $this->input->post('type');
        $cl_id = $this->input->post('cl_id');

        $data = array(
            'date_completed' => $complete_date,
            'status' => 'completed'
        );

        if ($action === "ongoing") {
            $data['status'] = 'ongoing';
            $data['date_completed'] = NULL;
        } else if ($action === "overdue") {
            $data['status'] = 'overdue';
            $data['date_completed'] = $due_date;
        }

        $this->db->where('id', $loan_id);
        if ($type === "fish") {
            $updated = $this->db->update('tbl_fish_transaction', $data);
        } else {
            $updated = $this->db->update('tbl_rice_transaction', $data);
        }

        if ($updated && $action === "overdue") {

            $new_due_date = date('Y-m-d', strtotime($new_start_date . ' +58 days'));

            $new_loan_details = array(
                'cl_id' => $cl_id,
                'capital_amt' => $running_bal,
                'interest' => $interest,
                'added_amt' => $added_amt,
                'total_amt' => $total_amt,
                'start_date' => $new_start_date,
                'due_date' => $new_due_date
            );

            $inserted = $this->db->insert('tbl_loan', $new_loan_details);
        }

        $inserted = false;

        if (!$updated) {
            echo json_encode(['status' => 'error']);
        } else {
            echo json_encode(['status' => 'success', 'data' => $inserted]);
        }
    }

    public function add_new_loan_same_client()
    {
        $cl_id = $this->input->post('cl_id');
        $capital_amt = $this->input->post('capital_amt');
        $interest = $this->input->post('interest');
        $added_amt = $this->input->post('added_amt');
        $total_amt = $this->input->post('total_amt');
        $start_date = $this->input->post('start_date');

        $due_date = date('Y-m-d', strtotime($start_date . ' +58 days'));

        $new_loan_details = array(
            'cl_id' => $cl_id,
            'capital_amt' => $capital_amt,
            'interest' => $interest,
            'added_amt' => $added_amt,
            'total_amt' => $total_amt,
            'start_date' => $start_date,
            'due_date' => $due_date
        );

        $inserted = $this->db->insert('tbl_loan', $new_loan_details);

        if ($inserted) {
            echo json_encode([
                'status' => 'success',
                'message' => 'New Loan inserted successfully.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to insert new loan.'
            ]);
        }
    }

    public function update_loan_data()
    {
        $id = $this->input->post('loan_id');

        $loan_details = array(
            'capital_amt' => $this->input->post('header_capital_amt'),
            'interest' => $this->input->post('header_interest'),
            'added_amt' => $this->input->post('header_added_amt'),
            'total_amt' => $this->input->post('header_total_amt'),
            'start_date' => $this->input->post('header_loan_date'),
            'due_date' => $this->input->post('header_due_date')
        );

        $this->db->where('id', $id);
        $updated = $this->db->update('tbl_loan', $loan_details);

        if (!$updated) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update loan details.'
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Loan updated successfully.'
            ]);
        }
    }

    public function delete_id()
    {
        $id = $this->input->post('id');

        $data = [
            'status' => "1"
        ];

        $this->db->where('id', $id);
        $updated = $this->db->update('tbl_client', $data);

        if (!$updated) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to delete client record.'
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Client data deleted successfully.',
            ]);
        }
    }

    public function recover_id()
    {
        $id = $this->input->post('id');
        $type = $this->input->post('type');

        $this->db->where('id', $id);

        $status = [
            'status' => '0'
        ];

        if ($type === "client") {
            $recovered = $this->db->update('tbl_client', $status);
        } else if ($type === "pull_out") {
            $recovered = $this->db->update('tbl_pull_out', $status);
        } else {
            $recovered = $this->db->update('tbl_expenses', $status);
        }

        if ($recovered && $type === "pull_out") {
            $record = $this->db->select('total_pull_out')
                ->where('id', $id)
                ->get('tbl_pull_out')
                ->row();

            $total_pull_out = floatval($record->total_pull_out);

            $this->db->set('pull_out_bal', 'pull_out_bal + ' . $total_pull_out, FALSE)
                ->where('id', 1)
                ->update('tbl_balance');
        }

        if ($recovered) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Data recovered successfully.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to recover data.'
            ]);
        }
    }

    public function get_daily_report()
    {
        $selectedDate = $this->input->post('date');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(17);
        $sheet->getColumnDimension('H')->setWidth(15);

        $loanData = $this->get_daily_data($selectedDate);

        $formattedDate = date('F j, Y', strtotime($selectedDate));
        $excelDateHeader = Date::PHPToExcel(strtotime($selectedDate));
        $previousDay = Date::PHPToExcel(strtotime($selectedDate . ' -1 day'));

        $data = [
            [$formattedDate],
            ["AREA 4'-Payment", "", "", ""],
            ["Processing Fee", "", "", "", "", "", "RP LENDING SERVICES", ""],
            ["EXCESS", "", "", "", "", "Area 4", "", ""],
            ["T O T A L - C P", "", "", "", "", "LEAH MAE GUCOR", "", ""],
            ["LESS : E X P E N S E S", "", "", "", "", "CASH COUNT", "", ""],
            ["Gas", "", "", "", "", "PIECES", "DENOMINATION", "AMOUNT"],
            ["Motor Shop", "", "", "", "", "", "", ""],
            ["Others", "", "", "", "", "", "", ""],
            ["", "", "", "", "", "", "", ""],
            ["", "", "", "", "", "", "", ""],
            ["", "", "", "", "", "", "", ""],
            ["", "", "", "", "", "", "", ""],
            ["T O T A L E X P E N S E S", "", "", "", "", "", "", ""],
            ["Collector's Cash Remitt", "", $excelDateHeader, "", "", "", "", ""],
            ["Ending Cash on Hand", "", $previousDay, "", "", "", "", ""],
            ["", "", "", "", "", "", "", ""],
            ["TOTAL MONEY", "", "", "", "", "TOTAL", "", ""],
            ["LESS(RELEASED)", "", "", ""],
            ["Date", "Name", "", "Amount"],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["TOTAL RELEASED", "", "", ""],
            ["", "", "", ""],
            ["LESS(PULLOUT)", "", "", ""],
            ["Capital", "10 % Profit Sharing", "Ticket", "Amount"],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["TOTAL PULLOUT", "", "", ""],
            ["ENDING CASH ONHAND", "", "", ""],
        ];

        // Write data to sheet
        $rowNumber = 1;
        foreach ($data as $row) {
            $col = 'A';
            foreach ($row as $cell) {
                $sheet->setCellValue($col . $rowNumber, $cell);
                $col++;
            }
            $rowNumber++;
        }

        // Populate loan release data starting from Excel row 21
        $excelRow = 21;

        foreach ($loanData as $loan) {
            if ($excelRow > 32)
                break;

            $timestamp = strtotime($loan['start_date']);
            $excelDate = Date::PHPToExcel($timestamp);

            // Convert full name to title case
            $fullName = ucwords(strtolower($loan['full_name']));

            $sheet->setCellValue('A' . $excelRow, $excelDate);
            $sheet->getStyle('A' . $excelRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('B' . $excelRow, $fullName); // Use the capitalized version
            $sheet->setCellValue('D' . $excelRow, (float) $loan['capital_amt']);
            $sheet->getStyle('D' . $excelRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $excelRow++;
        }

        // Calculate TOTAL RELEASED in Excel row 33
        $totalReleased = array_sum(array_column($loanData, 'capital_amt'));
        $sheet->setCellValue('D33', (float) $totalReleased);

        // Apply number formatting to ALL number cells
        $numberCells = [
            'D1',
            'D2',
            'D3',
            'D4',
            'D5',
            'D6',
            'D7',
            'D8',
            'D9',
            'D10',
            'D11',
            'D12',
            'D13',
            'D14',
            'D15',
            'D16',
            'D17',
            'D18',
            'D19',
            'D20',
            'D21',
            'D22',
            'D23',
            'D24',
            'D25',
            'D26',
            'D27',
            'D28',
            'D29',
            'D30',
            'D31',
            'D32',
            'D33',
            'D34',
            'D35',
            'D36',
            'D37',
            'D38',
            'D39',
            'D40',
            'D41',
            'D42',
            'D43',
            'A37',
            'A38',
            'A39',
            'A40',
            'A41',
            'B37',
            'B38',
            'B39',
            'B40',
            'B41',
            'C37',
            'C38',
            'C39',
            'C40',
            'C41',
            'G8',
            'G9',
            'G10',
            'G11',
            'G12',
            'G13',
            'G14',
            'G15',
            'G16',
            'G17',
            'G18',
            'H8',
            'H9',
            'H10',
            'H11',
            'H12',
            'H13',
            'H14',
            'H15',
            'H16',
            'H17',
            'H18',

        ];

        foreach ($numberCells as $cell) {
            $sheet->getStyle($cell)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            $sheet->getStyle($cell)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Apply date formatting to date cells
        $dateCells = [
            'A21',
            'A22',
            'A23',
            'A24',
            'A25',
            'A26',
            'A27',
            'A28',
            'A29',
            'A30',
            'A31',
            'A32',
            'C15',
            'C16'
        ];
        foreach ($dateCells as $cell) {
            $sheet->getStyle($cell)->getNumberFormat()
                ->setFormatCode('mm/dd/yyyy');
        }

        // Merge cells
        for ($row = 1; $row <= 14; $row++) {
            $sheet->mergeCells('A' . $row . ':C' . $row);
        }
        $sheet->mergeCells('G3:H3');
        $sheet->mergeCells('F5:H5');
        $sheet->mergeCells('F6:H6');
        for ($row = 15; $row <= 16; $row++) {
            $sheet->mergeCells('A' . $row . ':B' . $row);
        }
        $sheet->mergeCells('A17:D17');
        for ($row = 18; $row <= 19; $row++) {
            $sheet->mergeCells('A' . $row . ':C' . $row);
        }
        $sheet->mergeCells('F18:G18');
        for ($row = 20; $row <= 32; $row++) {
            $sheet->mergeCells('B' . $row . ':C' . $row);
        }
        $sheet->mergeCells('A33:C33');
        $sheet->mergeCells('A34:D34');
        $sheet->mergeCells('A35:C35');
        $sheet->mergeCells('A42:C42');
        $sheet->mergeCells('A43:C43');

        // Apply center alignment
        $sheet->getStyle('A1:C14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F3:H18')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F18:G18')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A15:B16')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C15:C16')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A18:C19')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A20:D20')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A21:A32')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C21:C32')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A33:C33')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A35:C35')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A36:C36')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A37:C41')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A42:C42')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A43:C43')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // $sheet->getStyle('E:E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style for title row
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('6ECF50');

        $sheet->getStyle('D15')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('FF66CC');

        $sheet->getStyle('D43')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('FF66CC');

        $dangerCells = [
            'A6',
            'D7',
            'D8',
            'D9',
            'D10',
            'D11',
            'D12',
            'D13',
            'D14',
            'A14',
            'A19',
            'D33',
            'A35',
            'D42',
        ];

        foreach ($dangerCells as $cell) {
            $sheet->getStyle($cell)->getFont()
                ->setBold(true)
                ->getColor()->setARGB(Color::COLOR_RED);
        }

        // Bold rows
        $totalRows = ['D2', 'D3', 'D4', 'D5', 'D15', 'D16', 'A18', 'D16', 'D18', 'A20', 'B20', 'D20', 'A33', 'A36', 'B36', 'C36', 'D36', 'A42', 'A43', 'F7', 'G7', 'H7', 'F18', 'H18', 'G3', 'F4', 'F5', 'F6', 'A5', 'A15', 'A16', 'D43', 'A2'];
        foreach ($totalRows as $cell) {
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Borders - UPDATED ROW NUMBERS
        $sheet->getStyle('A1:D19')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('F3:H5')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('F6:H6')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('F18:G18')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('H18:H18')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('F7:H17')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A20:D32')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A33:D33')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A34:D35')->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A36:D41')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A42:D42')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A43:D43')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        $lastRow = 4; // Row just before D5
        $sheet->setCellValue('D5', '=SUM(D2:D' . $lastRow . ')');

        $lastRow = 13; // Row just before D14
        $sheet->setCellValue('D14', '=SUM(D7:D' . $lastRow . ')');

        $sheet->setCellValue('D15', '=D5-D14');

        $sheet->setCellValue('D18', '=SUM(D15,D16)');

        $lastRow = 32; // Row just before D33
        $sheet->setCellValue('D33', '=SUM(D21:D' . $lastRow . ')');

        $sheet->setCellValue('D37', '=SUM(A37,B37,C37)');
        $sheet->setCellValue('D38', '=SUM(A38,B38,C38)');
        $sheet->setCellValue('D39', '=SUM(A39,B39,C39)');
        $sheet->setCellValue('D40', '=SUM(A40,B40,C40)');
        $sheet->setCellValue('D41', '=SUM(A41,B41,C41)');

        $lastRow = 41; // Row just before D42
        $sheet->setCellValue('D42', '=SUM(D37:D' . $lastRow . ')');

        // $sheet->setCellValue('D43', '=SUM(D18,D33,D42)');
        $sheet->setCellValue('D43', '=D18-D33-D42');

        $denominationValues = [1000, 500, 200, 100, 50, 20, 10, 5, 1, 0.25];

        for ($row = 8; $row <= 17; $row++) {
            $index = $row - 8;

            // Set fixed H (denomination)
            $sheet->setCellValue('G' . $row, $denominationValues[$index]);
            $sheet->getStyle('G' . $row)->getNumberFormat()
                ->setFormatCode('#,##0.00');

            // Set formula in I = G * H (dynamic)
            $sheet->setCellValue('H' . $row, '=IF(F' . $row . '="","",F' . $row . '*G' . $row . ')');
            $sheet->getStyle('H' . $row)->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        // Update total in I37
        $sheet->setCellValue('H18', '=SUM(H8:H17)');
        $sheet->getStyle('H18')->getNumberFormat()
            ->setFormatCode('#,##0.00');

        // $saveFolder = "C:/laragon/www/DAILY_REPORT";
        // if (!is_dir($saveFolder))
        //     mkdir($saveFolder, 0777, true);

        // $filePath = $saveFolder . "/" . $formattedDate . ".xlsx";

        // if (file_exists($filePath)) {
        //     // unlink($filePath); // Delete the existing file
        //     $response = [
        //         'status' => 'warning',
        //         'message' => 'Daily report for ' . $formattedDate . ' has already been generated.',
        //     ];

        //     echo json_encode($response);
        //     return;
        // }

        // $writer = new Xlsx($spreadsheet);
        // $writer->save($filePath);

        // if ($writer) {
        //     echo json_encode([
        //         'status' => 'success'
        //     ]);
        // } else {
        //     echo json_encode([
        //         'status' => 'error'
        //     ]);
        // }


        $writer = new Xlsx($spreadsheet);

        // Clear any previous output
        if (ob_get_length())
            ob_clean();

        // Set download headers - this triggers browser's Save As dialog
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Daily_Report_' . $formattedDate . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Save directly to browser - user gets Save As dialog
        $writer->save('php://output');
        // NO JSON response! This sends the Excel file directly.
        exit;
    }

    public function get_monthly_report()
    {
        $selectedDate = $this->input->post('date');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);

        $loanData = $this->get_monthly_data($selectedDate);
        $loanDataPayment = $this->get_monthly_data_payments($selectedDate);
        $expensesData = $this->get_monthly_expenses($selectedDate);

        $formattedDate = date('F Y', strtotime($selectedDate));

        $data = [
            [$formattedDate],
            ["Collection", "Release", "Interest", "Expenses"],
            ["", "", "", ""]

        ];

        $rowNumber = 1;
        foreach ($data as $row) {
            $col = 'A';
            foreach ($row as $cell) {
                $sheet->setCellValue($col . $rowNumber, $cell);
                $col++;
            }
            $rowNumber++;
        }

        $excelRow = 3;

        $sheet->setCellValue('A' . $excelRow, (float) $loanDataPayment['total_payment']);
        $sheet->getStyle('A' . $excelRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $excelRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        $sheet->setCellValue('B' . $excelRow, (float) $loanData['total_capital_amt']);
        $sheet->getStyle('B' . $excelRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $excelRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        $calculation = (float) $loanData['total_amt'] -
            (float) $loanData['total_capital_amt'] -
            (float) $loanData['total_added_amt'];

        $sheet->setCellValue('C' . $excelRow, $calculation);
        $sheet->getStyle('C' . $excelRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $excelRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        $sheet->setCellValue('D' . $excelRow, (float) $expensesData['total_expenses']);
        $sheet->getStyle('D' . $excelRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $excelRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1:D1' . $excelRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A1:D3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $totalRows = ['A2', 'B2', 'C2', 'D2'];
        foreach ($totalRows as $cell) {
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        $dangerCells = ['A3', 'B3', 'C3', 'D3'];

        foreach ($dangerCells as $cell) {
            $sheet->getStyle($cell)->getFont()
                ->setBold(true)
                ->getColor()->setARGB(Color::COLOR_RED);
        }

        // $saveFolder = "C:/laragon/www/MONTHLY_REPORT";
        // if (!is_dir($saveFolder))
        //     mkdir($saveFolder, 0777, true);

        // $filePath = $saveFolder . "/" . $formattedDate . ".xlsx";

        // if (file_exists($filePath)) {
        //     // unlink($filePath); // Delete the existing file
        //     $response = [
        //         'status' => 'warning',
        //         'message' => 'Monthly report for ' . $formattedDate . ' has already been generated.',
        //     ];

        //     echo json_encode($response);
        //     return;
        // }

        // $writer = new Xlsx($spreadsheet);
        // $writer->save($filePath);

        // if ($writer) {
        //     echo json_encode([
        //         'status' => 'success'
        //     ]);
        // } else {
        //     echo json_encode([
        //         'status' => 'error'
        //     ]);
        // }

        $writer = new Xlsx($spreadsheet);

        // Clear any previous output
        if (ob_get_length())
            ob_clean();

        // Set download headers - this triggers browser's Save As dialog
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Monthly_Report_' . $formattedDate . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Save directly to browser - user gets Save As dialog
        $writer->save('php://output');
        // NO JSON response! This sends the Excel file directly.
        exit;
    }

    public function formatWeekRange($selectedDate)
    {
        // Get Monday and Sunday of the week
        $monday = date('Y-m-d', strtotime('monday this week', strtotime($selectedDate)));
        $sunday = date('Y-m-d', strtotime('sunday this week', strtotime($selectedDate)));

        // Format the range
        $startMonth = date('M', strtotime($monday));
        $endMonth = date('M', strtotime($sunday));
        $startDay = date('j', strtotime($monday));
        $endDay = date('j', strtotime($sunday));
        $startYear = date('Y', strtotime($monday));
        $endYear = date('Y', strtotime($sunday));

        if ($startMonth === $endMonth && $startYear === $endYear) {
            // Same month and year: "Feb 9 - 14, 2026"
            return "$startMonth $startDay - $endDay, $startYear";
        } else if ($startYear === $endYear) {
            // Different months, same year: "Feb 28 - Mar 5, 2026"
            return "$startMonth $startDay - $endMonth $endDay, $startYear";
        } else {
            // Different years: "Dec 28, 2026 - Jan 3, 2027"
            return "$startMonth $startDay, $startYear - $endMonth $endDay, $endYear";
        }
    }
    public function get_weekly_report()
    {
        $selectedDate = $this->input->post('date');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(17);
        $sheet->getColumnDimension('H')->setWidth(15);

        $loanData = $this->get_weekly_data($selectedDate);
        $expData = $this->get_weekly_expenses($selectedDate);

        $totalAmount = $loanData['total_amt'];
        $totalCapitalAmount = $loanData['total_capital_amt'];
        $totalExpAmount = $expData['total_exp'];

        $weekRange = $this->formatWeekRange($selectedDate);

        $data = [
            [$weekRange],
            ["Payment", "", "", $totalAmount],
            ["Excess", "", "", ""],
            ["PAYMENT", "", "", ""],
            ["TOTAL COLLECTION", "", "", ""],
            ["Onhand Last Week", "", "", ""],
            ["", "", "", ""],
            ["Total Cash", "", "", ""],
            ["Release", "", "", "$totalCapitalAmount"],
            ["Operation Exp", "", "", "$totalExpAmount"],
            ["", "", "", ""],
            ["SALARY", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["TOTAL DEDUCTIONS", "", "", ""],
            ["", "", "", ""],
            ["NET CASH ONHAND", "", "", ""],
            ["", "", "", ""],
            ["Capital", "10 % Profit Sharing", "Ticket", "Amount"],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["", "", "", ""],
            ["TOTAL PULLOUT", "", "", ""],
            ["ENDING CASH ONHAND", "", "", ""],
        ];

        // Write data to sheet
        $rowNumber = 1;
        foreach ($data as $row) {
            $col = 'A';
            foreach ($row as $cell) {
                $sheet->setCellValue($col . $rowNumber, $cell);
                $col++;
            }
            $rowNumber++;
        }

        // Apply number formatting to ALL number cells
        $numberCells = [
            'D1',
            'D2',
            'D3',
            'D4',
            'D5',
            'D6',
            'D7',
            'D8',
            'D9',
            'D10',
            'D11',
            'D12',
            'D13',
            'D14',
            'D15',
            'D16',
            'D17',
            'D18',
            'D19',
            'D20',
            'D21',
            'D22',
            'D23',
            'D24',
            'D25',
            'D26',
            'D27'
        ];

        foreach ($numberCells as $cell) {
            $sheet->getStyle($cell)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            $sheet->getStyle($cell)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Merge cells
        $sheet->mergeCells('A1:D1');
        for ($row = 2; $row <= 16; $row++) {
            $sheet->mergeCells('A' . $row . ':C' . $row);
        }
        $sheet->mergeCells('A17:D17');
        $sheet->mergeCells('A18:C18');
        $sheet->mergeCells('A26:C26');
        $sheet->mergeCells('A27:C27');

        // Apply center alignment
        $sheet->getStyle('A1:C19')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A20:D25')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A26:D27')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style for title row
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('6ECF50');

        $sheet->getStyle('A5:D5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('FF66CC');

        $dangerCells = [
            'A16',
            'D16'
        ];

        foreach ($dangerCells as $cell) {
            $sheet->getStyle($cell)->getFont()
                ->setBold(true)
                ->getColor()->setARGB(Color::COLOR_RED);
        }

        $totalRows = ['A2', 'D2', 'A5', 'A6', 'D5', 'A8', 'D8', 'A18', 'D18', 'A20', 'B20', 'C20', 'D20', 'A26', 'A27', 'D26', 'D27'];
        foreach ($totalRows as $cell) {
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Borders - UPDATED ROW NUMBERS
        $sheet->getStyle('A1:D15')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('D16:D16')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('D18:D18')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        // $sheet->getStyle('D1:D15')->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A20:D25')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A26:D26')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A27:D27')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);


        $lastRow = 4; // Row just before D5
        $sheet->setCellValue('D5', '=SUM(D2:D' . $lastRow . ')');

        $sheet->setCellValue('D8', '=SUM(D5,D6)');

        $lastRow = 15; // Row just before D16
        $sheet->setCellValue('D16', '=SUM(D9:D' . $lastRow . ')');

        $sheet->setCellValue('D18', '=SUM(D15,D16)');

        $sheet->setCellValue('D18', '=D8-D16');

        $sheet->setCellValue('D21', '=SUM(A21,B21,C21)');
        $sheet->setCellValue('D22', '=SUM(A22,B22,C22)');
        $sheet->setCellValue('D23', '=SUM(A23,B23,C23)');
        $sheet->setCellValue('D24', '=SUM(A24,B24,C24)');
        $sheet->setCellValue('D25', '=SUM(A25,B25,C25)');

        $lastRow = 25; // Row just before D26
        $sheet->setCellValue('D26', '=SUM(D21:D' . $lastRow . ')');

        $sheet->setCellValue('D27', '=D18-D26');

        // $saveFolder = "C:/laragon/www/WEEKLY_REPORT";
        // if (!is_dir($saveFolder))
        //     mkdir($saveFolder, 0777, true);

        // $filePath = $saveFolder . "/" . $weekRange . ".xlsx";

        // if (file_exists($filePath)) {
        //     $response = [
        //         'status' => 'warning',
        //         'message' => 'Weekly report for ' . $weekRange . ' has already been generated.',
        //     ];

        //     echo json_encode($response);
        //     return;
        // }

        // $writer = new Xlsx($spreadsheet);
        // $writer->save($filePath);

        // if ($writer) {
        //     echo json_encode([
        //         'status' => 'success'
        //     ]);
        // } else {
        //     echo json_encode([
        //         'status' => 'error'
        //     ]);
        // }

        $writer = new Xlsx($spreadsheet);

        // Clear any previous output
        if (ob_get_length())
            ob_clean();

        // Set download headers - this triggers browser's Save As dialog
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Weekly_Report_' . $weekRange . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Save directly to browser - user gets Save As dialog
        $writer->save('php://output');
        // NO JSON response! This sends the Excel file directly.
        exit;
    }
    private function get_daily_data($selectedDate)
    {
        $this->db->select('
            a.capital_amt,
            a.start_date,
            b.full_name
        ');

        $this->db->from('tbl_loan as a');
        $this->db->join('tbl_client as b', 'b.id = a.cl_id');
        $this->db->where('a.start_date', $selectedDate);
        $this->db->where('b.status !=', '1');

        $query = $this->db->get();
        return $query->result_array();
    }

    // private function get_weekly_data($selectedDate)
    // {
    //     $monday = date('Y-m-d', strtotime('monday this week', strtotime($selectedDate)));
    //     $saturday = date('Y-m-d', strtotime('saturday this week', strtotime($selectedDate)));

    //     $this->db->select('
    //        sum(a.amt) as total_amt
    //     ');

    //     $this->db->from('tbl_payment as a');
    //     $this->db->join('tbl_loan as b', 'b.id = a.loan_id');
    //     $this->db->join('tbl_client as c', 'c.id = b.cl_id');
    //     $this->db->where('c.status !=', '1');
    //     $this->db->where("a.payment_for >=", $monday);
    //     $this->db->where("a.payment_for <=", $saturday);

    //     $query = $this->db->get();
    //     return $query->row_array();
    // }
    private function get_weekly_data($selectedDate)
    {
        $monday = date('Y-m-d', strtotime('monday this week', strtotime($selectedDate)));
        $sunday = date('Y-m-d', strtotime('sunday this week', strtotime($selectedDate)));

        // Query for payments (existing)
        $this->db->select('SUM(a.amt) as total_amt');
        $this->db->from('tbl_payment as a');
        $this->db->join('tbl_loan as b', 'b.id = a.loan_id');
        $this->db->join('tbl_client as c', 'c.id = b.cl_id');
        $this->db->where('c.status !=', '1');
        $this->db->where("a.payment_for >=", $monday);
        $this->db->where("a.payment_for <=", $sunday);
        $payment_query = $this->db->get();
        $payment_result = $payment_query->row_array();

        // Separate query for new loans created this week
        $this->db->select('SUM(capital_amt) as total_capital_amt');
        $this->db->from('tbl_loan');
        $this->db->join('tbl_client', 'tbl_client.id = tbl_loan.cl_id');
        $this->db->where('tbl_client.status !=', '1');
        $this->db->where("start_date >=", $monday);
        $this->db->where("start_date <=", $sunday);
        $loan_query = $this->db->get();
        $loan_result = $loan_query->row_array();

        return [
            'total_amt' => $payment_result['total_amt'] ?? 0,
            'total_capital_amt' => $loan_result['total_capital_amt'] ?? 0
        ];
    }

    private function get_weekly_expenses($selectedDate)
    {
        $monday = date('Y-m-d', strtotime('monday this week', strtotime($selectedDate)));
        $sunday = date('Y-m-d', strtotime('sunday this week', strtotime($selectedDate)));

        $this->db->select('
           sum(amt) as total_exp
        ');

        $this->db->from('tbl_expenses');
        $this->db->where('status !=', '1');
        $this->db->where("date_added >=", $monday);
        $this->db->where("date_added <=", $sunday);

        $query = $this->db->get();
        return $query->row_array();
    }

    private function get_monthly_data($selectedDate)
    {
        $startMonth = date('Y-m-01', strtotime($selectedDate));
        $endMonth = date('Y-m-t', strtotime($selectedDate));

        $this->db->select('
            SUM(a.capital_amt) as total_capital_amt,
            SUM(a.added_amt) as total_added_amt,
            SUM(a.total_amt) as total_amt
        ');

        $this->db->from('tbl_loan as a');
        $this->db->join('tbl_client as b', 'b.id = a.cl_id');

        $this->db->where('a.start_date >=', $startMonth);
        $this->db->where('a.start_date <=', $endMonth);
        $this->db->where('b.status !=', '1');

        return $this->db->get()->row_array();
    }

    private function get_monthly_data_payments($selectedDate)
    {
        $startMonth = date('Y-m-01', strtotime($selectedDate));
        $endMonth = date('Y-m-t', strtotime($selectedDate));

        $this->db->select('
            SUM(IFNULL(b.amt,0)) as total_payment
        ');

        $this->db->from('tbl_loan as a');
        $this->db->join('tbl_payment as b', 'b.loan_id = a.id');
        $this->db->join('tbl_client as c', 'c.id = a.cl_id');

        $this->db->where('b.payment_for >=', $startMonth);
        $this->db->where('b.payment_for <=', $endMonth);
        $this->db->where('c.status !=', '1');

        return $this->db->get()->row_array();
    }

    private function get_monthly_expenses($selectedDate)
    {
        $startMonth = date('Y-m-01', strtotime($selectedDate));
        $endMonth = date('Y-m-t', strtotime($selectedDate));

        $this->db->select('
            SUM(amt) as total_expenses
        ');

        $this->db->from('tbl_expenses');

        $this->db->where('date_added >=', $startMonth);
        $this->db->where('date_added <=', $endMonth);
        $this->db->where('status !=', '1');

        return $this->db->get()->row_array();
    }
    //     public function get_bulk_payment()
//     {
//         $date = $this->input->post('date');

    //         // First, get all loans starting on the selected date
//         $this->db->select('a.id as loan_id, a.start_date, a.due_date, a.total_amt, b.id as client_id, b.full_name, b.acc_no');
//         $this->db->from('tbl_loan as a');
//         $this->db->join('tbl_client as b', 'b.id = a.cl_id');
//         $this->db->where('a.start_date', $date);
//         $this->db->where('a.status', 'ongoing');
//         $this->db->where('b.status !=', '1');
//         $loans_query = $this->db->get();
//         $loans = $loans_query->result_array();

    //         $clients = [];
//         $all_dates = [];

    //         foreach ($loans as $loan) {
//             $client_id = $loan['client_id'];
//             $loan_id = $loan['loan_id'];
//             $total_loan_amount = $loan['total_amt'];
//             $start_date = $loan['start_date'];
//             $due_date = $loan['due_date'];

    //             // Initialize client if not exists
//             if (!isset($clients[$client_id])) {
//                 $clients[$client_id] = [
//                     'acc_no' => $loan['acc_no'],
//                     'full_name' => $loan['full_name'],
//                     'start_date' => $start_date,
//                     'due_date' => $due_date,
//                     'loan_id' => $loan_id,
//                     'client_id' => $client_id,
//                     'total_loan_amount' => floatval($total_loan_amount),
//                     'total_paid' => 0,
//                     'running_balance' => floatval($total_loan_amount),
//                     'payments' => []
//                 ];

    //                 // Generate date range (start_date +1 to due_date)
//                 $start = new DateTime($start_date);
//                 $start->modify('+1 day');
//                 $due = new DateTime($due_date);

    //                 $current_date = clone $start;
//                 while ($current_date <= $due) {
//                     $date_str = $current_date->format('Y-m-d');
//                     $clients[$client_id]['payments'][$date_str] = 0;
//                     $all_dates[$date_str] = $date_str;
//                     $current_date->modify('+1 day');
//                 }
//             }

    //             // Get payments for this loan WITHIN the date range (start_date+1 to due_date)
//             $this->db->select('payment_for, amt');
//             $this->db->from('tbl_payment');
//             $this->db->where('loan_id', $loan_id);

    //             // Add date range condition: payment_for BETWEEN start_date+1 AND due_date
//             $range_start = date('Y-m-d', strtotime($start_date . ' +1 day'));
//             $this->db->where("payment_for >=", $range_start);
//             $this->db->where("payment_for <=", $due_date);

    //             $payments_query = $this->db->get();
//             $payments = $payments_query->result_array();

    //             // Add payments to client's payment array
//             foreach ($payments as $payment) {
//                 $payment_date = $payment['payment_for'];
//                 $amount = floatval($payment['amt']);

    //                 if ($payment_date && $amount !== null && isset($clients[$client_id]['payments'][$payment_date])) {
//                     $clients[$client_id]['payments'][$payment_date] += $amount;
//                     $clients[$client_id]['total_paid'] += $amount;
//                     $clients[$client_id]['running_balance'] -= $amount;
//                 }
//             }
//         }
// +
//         // Sort dates
//         ksort($all_dates);

    //         // Prepare response
//         $response = [
//             'data' => array_values($clients),
//             'date_columns' => array_values($all_dates)
//         ];

    //         echo json_encode($response);
//     }

    // public function get_bulk_payment()
    // {
    //     $date = $this->input->post('date');

    //     $datePlusOne = date('Y-m-d', strtotime($date . ' -1 day'));

    //     $this->db->select('
    //         a.id as loan_id,
    //         a.start_date,
    //         a.due_date,
    //         b.id as client_id,
    //         b.full_name,
    //         b.acc_no
    //     ');

    //     $this->db->from('tbl_loan as a');
    //     $this->db->join('tbl_client as b', 'b.id = a.cl_id');
    //     $this->db->where("'$datePlusOne' BETWEEN a.start_date AND a.due_date");
    //     $this->db->where('a.status', 'ongoing');
    //     $this->db->where('b.status !=', '1');
    //     $this->db->order_by('b.acc_no', 'ASC');

    //     $query = $this->db->get();
    //     $clients = $query->result_array();

    //     foreach ($clients as &$client) {
    //         $loan_id = $client['loan_id'];

    //         $this->db->select('amt');
    //         $this->db->from('tbl_payment');
    //         $this->db->where('loan_id', $loan_id);
    //         $this->db->where('payment_for', $date);
    //         $payment_query = $this->db->get();

    //         if ($payment_query->num_rows() > 0) {
    //             $payment = $payment_query->row();
    //             $client['amt'] = floatval($payment->amt);
    //         } else {
    //             $client['amt'] = 0;
    //         }
    //     }

    //     $response = [
    //         'data' => $clients
    //     ];

    //     echo json_encode($response);
    // }

    public function get_bulk_payment()
    {
        $date = $this->input->post('date');

        // Calculate previous day
        $previous_date = date('Y-m-d', strtotime($date . ' -1 day'));

        $response = [
            'fish_data' => [],
            'rice_data' => []
        ];

        $this->db->select('
        a.id as loan_id,
        b.id as client_id,
        b.full_name,
        b.acc_no
    ');

        $this->db->from('tbl_fish_transaction as a');
        $this->db->join('tbl_client as b', 'b.id = a.cl_id');
        $this->db->where('a.status', 'ongoing');
        $this->db->where("'$previous_date' BETWEEN a.date_added AND a.due_date", NULL, FALSE);
        $this->db->where('b.status !=', '1');
        $this->db->order_by('b.acc_no', 'ASC');
        $this->db->group_by('a.id');

        $fish_query = $this->db->get();
        $fish_clients = $fish_query->result_array();

        foreach ($fish_clients as &$client) {
            $loan_id = $client['loan_id'];

            // Check if payment already exists for this date
            $this->db->select('amt');
            $this->db->from('tbl_payment');
            $this->db->where('trans_id', $loan_id);
            $this->db->where('payment_for', $date);
            $this->db->where('type', 'fish');
            $payment_query = $this->db->get();

            if ($payment_query->num_rows() > 0) {
                $payment = $payment_query->row();
                $client['amt'] = floatval($payment->amt);
            } else {
                $client['amt'] = 0;
            }
        }

        $response['fish_data'] = $fish_clients;

        // Get RICE credits
        $this->db->select('
        a.id as loan_id,
        b.id as client_id,
        b.full_name,
        b.acc_no
    ');

        $this->db->from('tbl_rice_transaction as a');
        $this->db->join('tbl_client as b', 'b.id = a.cl_id');
        $this->db->where('a.status', 'ongoing');
        $this->db->where("'$previous_date' BETWEEN a.date_added AND a.due_date", NULL, FALSE);
        $this->db->where('b.status !=', '1');
        $this->db->order_by('b.acc_no', 'ASC');
        $this->db->group_by('a.id');

        $rice_query = $this->db->get();
        $rice_clients = $rice_query->result_array();

        foreach ($rice_clients as &$client) {
            $loan_id = $client['loan_id'];

            // Check if payment already exists for this date
            $this->db->select('amt');
            $this->db->from('tbl_payment');
            $this->db->where('trans_id', $loan_id);
            $this->db->where('payment_for', $date);
            $this->db->where('type', 'rice');
            $payment_query = $this->db->get();

            if ($payment_query->num_rows() > 0) {
                $payment = $payment_query->row();
                $client['amt'] = floatval($payment->amt);
            } else {
                $client['amt'] = 0;
            }
        }

        $response['rice_data'] = $rice_clients;

        echo json_encode($response);
    }

    // public function save_bulk_payments()
    // {
    //     // Get JSON data
    //     $json_data = file_get_contents('php://input');
    //     $data = json_decode($json_data, true);

    //     $selected_date = $data['selected_date'] ?? null;
    //     $payments = $data['payments'] ?? [];
    //     $updated_balances = $data['updated_balances'] ?? []; // Get updated balances from frontend

    //     if (empty($selected_date) || empty($payments)) {
    //         echo json_encode(['success' => false, 'message' => 'No data to save.']);
    //         return;
    //     }

    //     $success_count = 0;
    //     $error_count = 0;
    //     $completed_loans = [];
    //     $errors = [];

    //     // Start transaction for data consistency
    //     $this->db->trans_start();

    //     foreach ($payments as $payment) {
    //         $client_id = $payment['client_id'] ?? null;
    //         $loan_id = $payment['loan_id'] ?? null;
    //         $date = $payment['date'] ?? null;
    //         $amount = $payment['amount'] ?? 0;

    //         if (!$client_id || !$loan_id || !$date || $amount <= 0) {
    //             $error_count++;
    //             continue;
    //         }

    //         // Check if payment already exists for this date and loan
    //         $this->db->where('loan_id', $loan_id);
    //         $this->db->where('payment_for', $date);
    //         $existing = $this->db->get('tbl_payment')->row();

    //         if ($existing) {
    //             // Update existing payment
    //             $this->db->where('id', $existing->id);
    //             $update_data = [
    //                 'amt' => $amount
    //             ];

    //             if ($this->db->update('tbl_payment', $update_data)) {
    //                 $success_count++;
    //             } else {
    //                 $error_count++;
    //                 $errors[] = "Failed to update payment for client $client_id on $date";
    //             }
    //         } else {
    //             // Insert new payment
    //             $insert_data = [
    //                 'loan_id' => $loan_id,
    //                 'payment_for' => $date,
    //                 'amt' => $amount
    //             ];

    //             if ($this->db->insert('tbl_payment', $insert_data)) {
    //                 $success_count++;
    //             } else {
    //                 $error_count++;
    //                 $errors[] = "Failed to insert payment for client $client_id on $date";
    //             }
    //         }
    //     }

    //     // Process updated balances to mark loans as completed if balance is 0
    //     foreach ($updated_balances as $balance) {
    //         $loan_id = $balance['loan_id'] ?? null;
    //         $running_balance = $balance['running_balance'] ?? null;

    //         if ($loan_id && $running_balance !== null) {

    //             // Check if running balance is 0 or less (fully paid)
    //             if ($running_balance <= 0) {
    //                 // Get the last payment date for this loan
    //                 $this->db->select('MAX(payment_for) as last_payment_date');
    //                 $this->db->from('tbl_payment');
    //                 $this->db->where('loan_id', $loan_id);
    //                 $last_payment_query = $this->db->get();
    //                 $last_payment = $last_payment_query->row();

    //                 $complete_date = $last_payment->last_payment_date ?? date('Y-m-d');

    //                 // Update loan status to 'completed'
    //                 $this->db->where('id', $loan_id);
    //                 $loan_update = $this->db->update('tbl_loan', [
    //                     'status' => 'completed',
    //                     'complete_date' => $complete_date
    //                 ]);

    //                 if ($loan_update) {
    //                     $completed_loans[] = [
    //                         'loan_id' => $loan_id,
    //                         'complete_date' => $complete_date
    //                     ];
    //                 }
    //             }
    //         }
    //     }

    //     $this->db->trans_complete();

    //     if ($this->db->trans_status() === FALSE) {
    //         $this->db->trans_rollback();
    //         echo json_encode([
    //             'success' => false,
    //             'message' => 'Transaction failed. No payments were saved.'
    //         ]);
    //     } else {
    //         $response = [
    //             'success' => true,
    //             'message' => "Successfully saved $success_count payments. Failed: $error_count",
    //             'saved_count' => $success_count,
    //             'failed_count' => $error_count,
    //             'errors' => $errors
    //         ];

    //         // Add completed loans info if any
    //         if (!empty($completed_loans)) {
    //             $response['completed_loans'] = $completed_loans;
    //             $response['completed_count'] = count($completed_loans);
    //             $response['message'] .= " " . count($completed_loans) . " loan(s) marked as completed.";
    //         }

    //         echo json_encode($response);
    //     }
    // }

    // public function save_bulk_payments()
    // {
    //     $date = $this->input->post('date');
    //     $payments = $this->input->post('payments');

    //     $success_count = 0;
    //     $error_count = 0;

    //     if ($payments && is_array($payments)) {
    //         foreach ($payments as $payment) {

    //             if (empty($payment['client_id']) || empty($payment['loan_id']) || empty($payment['amount'])) {
    //                 $error_count++;
    //                 continue;
    //             }

    //             $payment_data = array(
    //                 'loan_id' => $payment['loan_id'],
    //                 'payment_for' => $date,
    //                 'amt' => $payment['amount'],
    //             );

    //             $this->db->where('loan_id', $payment['loan_id']);
    //             $this->db->where('payment_for', $date);
    //             $existing = $this->db->get('tbl_payment')->row();

    //             if ($existing) {
    //                 $this->db->where('loan_id', $payment['loan_id']);
    //                 $this->db->where('payment_for', $date);

    //                 if ($this->db->update('tbl_payment', $payment_data)) {
    //                     $success_count++;
    //                 } else {
    //                     $error_count++;
    //                 }

    //             } else {
    //                 if ($this->db->insert('tbl_payment', $payment_data)) {
    //                     $success_count++;
    //                 } else {
    //                     $error_count++;
    //                 }
    //             }
    //         }
    //     }

    //     $response = array(
    //         'success' => true,
    //         'message' => "Successfully saved $success_count payment(s). " . ($error_count > 0 ? "$error_count payment(s) failed." : "")
    //     );

    //     echo json_encode($response);
    // }

    public function save_bulk_payments()
    {
        $payments = $this->input->post('payments');
        $date = $this->input->post('date');

        if (empty($payments)) {
            echo json_encode(['success' => false, 'message' => 'No payments to save']);
            return;
        }

        $this->db->trans_start();

        foreach ($payments as $payment) {
            $data = array(
                'trans_id' => $payment['loan_id'],
                'amt' => $payment['amount'],
                'payment_for' => $date,
                'type' => $payment['type'],
                'date_added' => date('Y-m-d H:i:s')
            );

            $this->db->insert('tbl_payment', $data);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => false, 'message' => 'Failed to save payments']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Payments saved successfully']);
        }
    }

    public function add_variance()
    {
        $over = $this->input->post('over');
        $short = $this->input->post('short');
        $date = $this->input->post('date');

        $varianceData = [
            'over' => $over,
            'short' => $short,
            'date_added' => $date
        ];

        $inserted = $this->db->insert('tbl_variance', $varianceData);

        if ($inserted) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Variance inserted successfully.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to insert variance.'
            ]);
        }
    }

    public function get_variance_data()
    {
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $searchValue = trim($this->input->post('search')['value']);

        $order = $this->input->post('order');
        $orderColumnIndex = isset($order[0]['column']) ? $order[0]['column'] : 0;
        $orderDir = isset($order[0]['dir']) ? $order[0]['dir'] : 'DESC';

        $columns = [
            0 => 'date_added',
            1 => 'over',
            2 => 'short'
        ];

        $orderColumn = $columns[$orderColumnIndex];

        // Base query for totals - escape reserved keyword 'over'
        $totalQuery = clone $this->db;
        $totalQuery->select('
        SUM(`over`) as total_over,
        SUM(short) as total_short
    ');
        $totalQuery->from('tbl_variance');

        if (!empty($searchValue)) {
            $totalQuery->group_start();
            $totalQuery->like('date_added', $searchValue);
            $totalQuery->or_like('`over`', $searchValue);  // Escape 'over' here too
            $totalQuery->or_like('short', $searchValue);
            $totalQuery->group_end();
        }

        $totals = $totalQuery->get()->row();
        $totalOver = $totals->total_over ?: 0;
        $totalShort = $totals->total_short ?: 0;

        // Main query for paginated data - escape 'over' in select and like
        $this->db->select('
            id,
            date_added,
            `over`,
            short
        ');

        $this->db->from('tbl_variance');

        if (!empty($searchValue)) {
            $this->db->group_start();
            $this->db->like('date_added', $searchValue);
            $this->db->or_like('`over`', $searchValue);  // Escape 'over' here
            $this->db->or_like('short', $searchValue);
            $this->db->group_end();
        }

        $this->db->order_by($orderColumn, $orderDir);

        $subQuery = clone $this->db;
        $recordsFiltered = $subQuery->get()->num_rows();

        $this->db->limit($length, $start);

        $query = $this->db->get();
        $data = $query->result_array();

        echo json_encode([
            "draw" => intval($this->input->post('draw')),
            "recordsTotal" => $recordsFiltered,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
            "total_over" => $totalOver,
            "total_short" => $totalShort
        ]);
    }

    public function update_variance()
    {
        $id = $this->input->post('id');
        $date = $this->input->post('date');
        $over = $this->input->post('over');
        $short = $this->input->post('short');

        $data = [
            'date_added' => $date,
            'over' => $over,
            'short' => $short
        ];

        $this->db->where('id', $id);
        $result = $this->db->update('tbl_variance', $data);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Variance record updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update variance record']);
        }
    }

    public function delete_variance()
    {
        $id = $this->input->post('id');

        $this->db->where('id', $id);
        $result = $this->db->delete('tbl_variance');

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Variance record deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete variance record']);
        }
    }
    public function delete_loan_id()
    {
        $id = $this->input->post('id');

        $this->db->where('id', $id);
        $result = $this->db->delete('tbl_loan');

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Loan record deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete loan record']);
        }
    }

    public function save_multiple_credits()
    {
        try {
            $client_id = $this->input->post('client_id');
            $items = $this->input->post('items');
            $grand_total = $this->input->post('grand_total');

            // Validate required fields
            if (empty($client_id)) {
                throw new Exception('Client ID is required');
            }

            if (empty($items) || !is_array($items)) {
                throw new Exception('No items to save');
            }

            // Start transaction
            $this->db->trans_begin();

            $fishItems = [];
            $riceItems = [];

            // Separate fish and rice items
            foreach ($items as $item) {
                if ($item['type'] == 'dried_fish') {
                    $fishItems[] = $item;
                } elseif ($item['type'] == 'rice') {
                    $riceItems[] = $item;
                }
            }

            // Save Fish Transactions
            if (!empty($fishItems)) {
                // Calculate total subtotal for the main transaction
                $totalSubtotal = 0;
                $totalInterest = 0;
                $totalAddedAmt = 0;

                foreach ($fishItems as $fish) {
                    $totalSubtotal += floatval($fish['subtotal']);
                    $totalInterest += isset($fish['interest_rate']) ? floatval($fish['interest_rate']) : 0;
                    $totalAddedAmt += isset($fish['added_amount']) ? floatval($fish['added_amount']) : 0;
                }

                // Insert main fish transaction
                $fishTransactionData = array(
                    'cl_id' => $client_id,
                    'total_amt' => $totalSubtotal,
                    'status' => 'ongoing',
                    'date_added' => $fish['trans_date'],
                    'due_date' => date('Y-m-d', strtotime($fish['trans_date'] . ' + 15 days'))
                );

                $this->db->insert('tbl_fish_transaction', $fishTransactionData);
                $fishTransId = $this->db->insert_id();

                // Insert fish transaction details (with interest per item)
                foreach ($fishItems as $fish) {
                    $qtyInGrams = floatval($fish['quantity']) * 1000; // Convert kg to grams

                    $fishDetailData = array(
                        'trans_id' => $fishTransId,
                        'fish_id' => $fish['product_id'],
                        'qty' => $qtyInGrams,
                        'price_per_kg' => $fish['price_per_unit'],
                        'interest' => isset($fish['interest_rate']) ? $fish['interest_rate'] : 0,
                        'added_amt' => isset($fish['added_amount']) ? $fish['added_amount'] : 0,
                        'sub_total' => $fish['subtotal']
                    );

                    $this->db->insert('tbl_fish_transaction_details', $fishDetailData);

                    $fishStockData = array(
                        'fish_id' => $fish['product_id'],
                        'qty' => $qtyInGrams,
                        'trans_type' => 'out',
                        'trans_date' => $fish['trans_date'],
                    );

                    $this->db->insert('tbl_fish_stock', $fishStockData);
                }
            }

            // Save Rice Transactions using transaction detail rows
            if (!empty($riceItems)) {
                $totalRiceSubtotal = 0;
                $totalRiceQty = 0;

                foreach ($riceItems as $rice) {
                    $totalRiceSubtotal += floatval($rice['subtotal']);
                    $totalRiceQty += floatval($rice['quantity']);
                }

                // Use the first rice item to populate the main rice transaction rice_id field.
                $this->db->select('id');
                $this->db->from('tbl_rice');
                $this->db->where('rice_type', $riceItems[0]['product_name']);
                $this->db->where('sack_type', $riceItems[0]['sack_size'] . 'kg');
                $query = $this->db->get();
                $firstRiceRecord = $query->row();

                if (!$firstRiceRecord) {
                    throw new Exception('Rice type not found: ' . $riceItems[0]['product_name'] . ' (' . $riceItems[0]['sack_size'] . 'kg)');
                }

                $riceTransactionData = array(
                    'cl_id' => $client_id,
                    'total_amt' => $totalRiceSubtotal,
                    'date_added' => $rice['trans_date'],
                    'due_date' => date('Y-m-d', strtotime($rice['trans_date'] . ' + 30 days')),
                    'status' => 'ongoing'
                );

                $this->db->insert('tbl_rice_transaction', $riceTransactionData);
                $riceTransId = $this->db->insert_id();

                foreach ($riceItems as $rice) {
                    $this->db->select('id');
                    $this->db->from('tbl_rice');
                    $this->db->where('rice_type', $rice['product_name']);
                    $this->db->where('sack_type', $rice['sack_size'] . 'kg');
                    $query = $this->db->get();
                    $rice_record = $query->row();

                    if (!$rice_record) {
                        throw new Exception('Rice type not found: ' . $rice['product_name'] . ' (' . $rice['sack_size'] . 'kg)');
                    }

                    $riceDetailData = array(
                        'trans_id' => $riceTransId,
                        'rice_id' => $rice_record->id,
                        'qty' => $rice['quantity'],
                        'price_per_kg' => $rice['price_per_unit'],
                        'interest' => isset($rice['interest_rate']) ? $rice['interest_rate'] : 0,
                        'added_amt' => isset($rice['added_amount']) ? $rice['added_amount'] : 0,
                        'sub_total' => $rice['subtotal']
                    );

                    $this->db->insert('tbl_rice_transaction_details', $riceDetailData);

                    $riceStockData = array(
                        'rice_id' => $rice_record->id,
                        'qty' => $rice['quantity'],
                        'trans_type' => 'out',
                        'trans_date' => $rice['trans_date'],
                    );

                    $this->db->insert('tbl_rice_stock', $riceStockData);
                }
            }

            // Commit transaction
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                throw new Exception('Database transaction failed');
            } else {
                $this->db->trans_commit();
                echo json_encode(['success' => true, 'message' => 'Credits saved successfully']);
            }

        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function get_loan_date_range()
    {
        $id = $this->input->post('client_id');

        $response = array();

        $this->db->select('id, date_added, due_date, status, total_amt, date_completed');
        $this->db->from('tbl_fish_transaction');
        $this->db->where('cl_id', $id);
        $this->db->order_by('id', 'DESC');

        $fish_query = $this->db->get();
        $response['fish'] = $fish_query->result();

        $this->db->select('id, date_added, due_date, status, total_amt, date_completed');
        $this->db->from('tbl_rice_transaction');
        $this->db->where('cl_id', $id);
        $this->db->order_by('id', 'DESC');

        $rice_query = $this->db->get();
        $response['rice'] = $rice_query->result();

        echo json_encode($response);
    }

    public function get_fish_loan_details()
    {
        $id = $this->input->post('loan_id');

        $this->db->select('
            b.fish_type,
            a.qty,
            a.price_per_kg,
            a.interest,
            a.added_amt,
            a.sub_total
        ');

        $this->db->from('tbl_fish_transaction_details as a');
        $this->db->join('tbl_fish as b', 'a.fish_id = b.id');
        $this->db->where('a.trans_id', $id);

        $query = $this->db->get();
        echo json_encode($query->result());
    }
    public function get_rice_loan_details()
    {
        $id = $this->input->post('loan_id');

        // Validate input
        if (empty($id)) {
            echo json_encode(['error' => 'No loan ID provided']);
            return;
        }

        $this->db->select('
        b.rice_type,
        b.sack_type,
        a.qty,
        a.price_per_kg,
        a.interest,
        a.added_amt,
        a.sub_total
    ');

        $this->db->from('tbl_rice_transaction_details as a');
        $this->db->join('tbl_rice as b', 'a.rice_id = b.id', 'left');
        $this->db->where('a.trans_id', $id);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            echo json_encode($query->result());
        } else {
            echo json_encode(['error' => 'No records found']);
        }
    }

    public function get_payment_history_fish()
    {
        $id = $this->input->post('loan_id');

        $this->db->select('amt, payment_for');
        $this->db->from('tbl_payment');
        $this->db->where('trans_id', $id);
        $this->db->where('type', "fish");
        $query = $this->db->get();
        echo json_encode($query->result());
    }

    public function get_payment_history_rice()
    {
        $id = $this->input->post('loan_id');

        $this->db->select('amt, payment_for');
        $this->db->from('tbl_payment');
        $this->db->where('trans_id', $id);
        $this->db->where('type', "rice");
        $query = $this->db->get();
        echo json_encode($query->result());
    }

    public function update_due_date()
    {
        $id = $this->input->post('loan_id');
        $type = $this->input->post('type'); // 'fish' or 'rice'

        // Get current due_date based on type
        if ($type == 'rice') {
            $this->db->select('due_date');
            $this->db->from('tbl_rice_transaction');
            $this->db->where('id', $id);
            $query = $this->db->get();
            $loan = $query->row();

            if ($loan) {
                // New due_date = current due_date + 15 days
                $new_due_date = date('Y-m-d', strtotime($loan->due_date . ' + 30 days'));

                // Update the due_date and status
                $this->db->where('id', $id);
                $this->db->update('tbl_rice_transaction', [
                    'due_date' => $new_due_date,
                    'status' => 'overdue'
                ]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Rice due date extended by 15 days',
                    'new_due_date' => $new_due_date
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Rice loan not found'
                ]);
            }
        } else {
            // Default to fish
            $this->db->select('due_date');
            $this->db->from('tbl_fish_transaction');
            $this->db->where('id', $id);
            $query = $this->db->get();
            $loan = $query->row();

            if ($loan) {
                // New due_date = current due_date + 15 days
                $new_due_date = date('Y-m-d', strtotime($loan->due_date . ' + 15 days'));

                // Update the due_date and status
                $this->db->where('id', $id);
                $this->db->update('tbl_fish_transaction', [
                    'due_date' => $new_due_date,
                    'status' => 'overdue'
                ]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Fish due date extended by 15 days',
                    'new_due_date' => $new_due_date
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Fish loan not found'
                ]);
            }
        }
    }

    public function save_dried_fish_loan()
    {
        $client_id = $this->input->post('client_id');
        $transaction_date = $this->input->post('transaction_date');
        $items = $this->input->post('items');
        $grand_total = $this->input->post('grand_total');

        if (!$client_id || !$transaction_date || empty($items)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        $due_date = date('Y-m-d', strtotime($transaction_date . ' + 15 days'));

        $this->db->trans_start();

        $loan_data = [
            'cl_id' => $client_id,
            'total_amt' => $grand_total,
            'date_added' => $transaction_date,
            'due_date' => $due_date,
            'status' => 'ongoing'
        ];

        $this->db->insert('tbl_fish_transaction', $loan_data);
        $loan_id = $this->db->insert_id();

        foreach ($items as $item) {
            $qtyInGrams = floatval($item['quantity']) * 1000;

            $item_data = [
                'trans_id' => $loan_id,
                'fish_id' => $item['fish_type'],
                'qty' => $qtyInGrams,
                'price_per_kg' => $item['price_per_kg'],
                'interest' => $item['interest_rate'],
                'added_amt' => $item['added_amount'],
                'sub_total' => $item['subtotal']
            ];
            $this->db->insert('tbl_fish_transaction_details', $item_data);

            $fishStockData = array(
                'fish_id' => $item['fish_type'],
                'qty' => $qtyInGrams,
                'trans_type' => 'out',
                'trans_date' => $transaction_date,
            );

            $this->db->insert('tbl_fish_stock', $fishStockData);
        }

        $this->db->trans_complete();

        echo json_encode([
            'success' => true,
            'message' => 'Loan saved successfully'
        ]);
    }

    public function save_rice_loan()
    {
        $client_id = $this->input->post('client_id');
        $transaction_date = $this->input->post('transaction_date');
        $items = $this->input->post('items');
        $grand_total = $this->input->post('grand_total');

        $due_date = date('Y-m-d', strtotime($transaction_date . ' + 30 days'));

        $this->db->trans_start();

        $loan_data = [
            'cl_id' => $client_id,
            'total_amt' => $grand_total,
            'date_added' => $transaction_date,
            'due_date' => $due_date,
            'status' => 'ongoing'
        ];

        $this->db->insert('tbl_rice_transaction', $loan_data);
        $loan_id = $this->db->insert_id();

        if (!$loan_id) {
            throw new Exception('Failed to create loan record');
        }

        foreach ($items as $item) {
            // Get rice_id by matching rice_type and sack_size
            $this->db->select('id');
            $this->db->where('rice_type', $item['rice_type']);
            $this->db->where('sack_type', $item['sack_size'] . 'kg');
            $query = $this->db->get('tbl_rice');

            if ($query->num_rows() == 0) {
                throw new Exception('Rice type not found: ' . $item['rice_type'] . ' with sack size: ' . $item['sack_size']);
            }

            $rice_id = $query->row()->id;

            $item_data = [
                'trans_id' => $loan_id,
                'rice_id' => $rice_id,
                'qty' => $item['quantity_sacks'],
                'price_per_kg' => $item['price_per_kg'],
                'interest' => $item['interest_rate'],
                'added_amt' => $item['added_amount'],
                'sub_total' => $item['subtotal']
            ];
            $this->db->insert('tbl_rice_transaction_details', $item_data);

            // Update rice stock (deduct)
            $riceStockData = array(
                'rice_id' => $rice_id,
                'qty' => $item['quantity_sacks'],
                'trans_type' => 'out',
                'trans_date' => $transaction_date,
            );

            $this->db->insert('tbl_rice_stock', $riceStockData);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Transaction failed');
        }

        echo json_encode([
            'success' => true,
            'message' => 'Rice loan saved successfully'
        ]);

    }

    public function auto_complete_payment()
    {
        $loan_id = $this->input->post('loan_id');
        $type = $this->input->post('type');
        $status = $this->input->post('status');

        if ($status === 'completed') {
            $data = [
                'status' => 'completed',
                'date_completed' => date('Y-m-d')
            ];
        } else if ($status === 'overdue') {
            $data = [
                'status' => 'completed',
                'date_completed' => date('Y-m-d')
            ];
        } else {
            $data = [
                'status' => 'ongoing',
                'date_completed' => null
            ];
        }

        $table = ($type === "fish") ? 'tbl_fish_transaction' : 'tbl_rice_transaction';

        $this->db->where('id', $loan_id);
        $this->db->update($table, $data);

        echo json_encode([
            'success' => true,
            'message' => ucfirst($type) . ' loan status updated to ' . strtoupper($status),
            'status' => $status
        ]);
    }

    public function get_loan_statuses()
    {
        $cl_id = $this->input->post('cl_id');

        $this->db->select('id, status');
        $this->db->from('tbl_fish_transaction');
        $this->db->where('cl_id', $cl_id);
        $fish_query = $this->db->get();

        $this->db->select('id, status');
        $this->db->from('tbl_rice_transaction');
        $this->db->where('cl_id', $cl_id);
        $rice_query = $this->db->get();

        echo json_encode([
            'success' => true,
            'fish_loans' => $fish_query->result(),
            'rice_loans' => $rice_query->result()
        ]);
    }

    public function delete_fish_loan_id()
    {
        $loan_id = $this->input->post('loan_id');

        $this->db->select('fish_id, qty');
        $this->db->from('tbl_fish_transaction_details');
        $this->db->where('trans_id', $loan_id);
        $query = $this->db->get();
        $fish_details = $query->result();

        $this->db->trans_start();

        foreach ($fish_details as $item) {

            $stock_data = array(
                'fish_id' => $item->fish_id,
                'qty' => $item->qty,
                'trans_type' => 'in',
                'trans_date' => date('Y-m-d')
            );

            $this->db->insert('tbl_fish_stock', $stock_data);
        }

        $this->db->where('trans_id', $loan_id);
        $this->db->delete('tbl_fish_transaction_details');

        $this->db->where('trans_id', $loan_id);
        $this->db->where('type', "fish");
        $this->db->delete('tbl_payment');

        $this->db->where('id', $loan_id);
        $this->db->delete('tbl_fish_transaction');

        $this->db->trans_complete();

        echo json_encode([
            'success' => true,
            'message' => 'Fish credit deleted successfully and stock updated'
        ]);
    }

    public function delete_rice_loan_id()
    {
        $loan_id = $this->input->post('loan_id');

        $this->db->select('rice_id, qty');
        $this->db->from('tbl_rice_transaction_details');
        $this->db->where('trans_id', $loan_id);
        $query = $this->db->get();
        $rice_details = $query->result();

        $this->db->trans_start();

        foreach ($rice_details as $item) {

            $stock_data = array(
                'rice_id' => $item->rice_id,
                'qty' => $item->qty,
                'trans_type' => 'in',
                'trans_date' => date('Y-m-d')
            );

            $this->db->insert('tbl_rice_stock', $stock_data);
        }

        $this->db->where('trans_id', $loan_id);
        $this->db->delete('tbl_rice_transaction_details');

        $this->db->where('trans_id', $loan_id);
        $this->db->where('type', "rice");
        $this->db->delete('tbl_payment');

        $this->db->where('id', $loan_id);
        $this->db->delete('tbl_rice_transaction');

        $this->db->trans_complete();

        echo json_encode([
            'success' => true,
            'message' => 'Rice credit deleted successfully and stock updated'
        ]);
    }
}
