<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Booker extends CI_Controller {

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     *	- or -
     * 		http://example.com/index.php/welcome/index
     *	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */
    function __construct(){
        parent::__construct();
        $this->load->model('book_model');
    }

    public function add(){
        $data['book_no'] = $_POST['book_no'];
        $data['book_title'] = $_POST['book_title'];
        $data['description'] = $_POST['description'];
        $data['author'] = $_POST['author'];
        $data['publisher'] = $_POST['publisher'];
        $data['date_published'] = $_POST['date_published'];
        $data['tags'] = $_POST['tags'];
        $this->load->library('form_validation');
        $this->form_validation->set_rules('book_no','BookNumber','required|is_unique[ics-lib-db.Book_no]|min_length[1]|alpha_numeric');
        $this->form_validation->set_rules('book_title','BookTitle','required' );
        $this->form_validation->set_rules('description','Description','required');
        $this->form_validation->set_rules('publisher','Publisher','required');
        $this->form_validation->set_rules('date_published','DatePublished','required');
        $this->form_validation->set_rules('tags','Tags','callback_tags_check');

        $this->book_model->insertBook($data);

        echo json_encode($_POST);
        /* ???
        if ($this->form_validation->run()==FALSE){

            $this->load->view('manage_view');

        }
        else{
            $this->load->view('views/index.html');
        }
        */

    }

    public function delete(){
        $book_no = $_POST['book_no'];
        $this->book_model->delBook($book_no);
    }

    public function edit(){
        $data['prev_book_no'] = $_POST['prev_book_no'];
        $data['book_no'] = $_POST['book_no'];
        $data['book_title'] = $_POST['book_title'];
        $data['description'] = $_POST['description'];
        $data['publisher'] = $_POST['publisher'];
        $data['date_published'] = $_POST['date_published'];

        $this->book_model->editBook($data);
    }

    public function display_views($data){

        $this->load->view('header',$data);
        $this->load->view('search_view');
        $this->load->view('table_view',$data);
        $this->load->view('manage_view',$data);
        $this->load->view('footer');
    }

    public function index(){
        $this->load->library('javascript');
        $data['title'] = "eICS Lib";

        $data['table'] = $this->search();

        if(isset($_POST['submit_del'])){
            $this->delete();
        }

        $this->display_views($data);
    }

    public function search(){
        //set defaults
        $search_term = "";
        $search_by = "book_title";
        $order_by = "a.book_no";
        $status_check = "status='available' or status='borrowed' or status='reserved'";

        if (isset($_POST["submit_search"])){
            //get user input

            $search_term = $_POST['search'];
            $search_by = $_POST['search_by'];
            $order_by = $_POST['order_by'];

            //filter by book status
            if (!isset($_POST["available"])) $status_check = str_replace("status='available' or ","",$status_check);
            if (!isset($_POST["borrowed"])) $status_check = str_replace("status='borrowed' or ","",$status_check);
            if (!isset($_POST["reserved"])){
                $status_check = str_replace(" or status='reserved'","",$status_check);
                $status_check = str_replace("status='reserved'","",$status_check);
            }

            if($search_by == "book_no")	$search_by = "a.book_no";
            if($order_by == "book_no") $order_by = "a.book_no";
        }


        if($status_check!="") $status_check = "(" . $status_check . ") and";

        $details = array(
            'search_term' 	=> $search_term,
            'search_by' 	=> $search_by,
            'order_by' 		=> $order_by,
            'status_check' 	=> $status_check,
        );


        $table = $this->book_model->query_result($details);
        if ($table == null) return null;
        foreach($table as $row):
                //echo $row->book_no;
        
                $table_copy[$row->book_no] = $row;

            //transfer contents of table to $sorted + structure of points
            $arr = explode(" ", $search_term);
            $booktitle = explode(" ", $row->book_title);
            $descr = explode(" ", $row->description);
            $tg = explode(",", $row->Tags);
            $points;

            // echo $arr[1];


            // foreach ($arr as $maila):
            $points[$row->book_no] = 0;
                // endforeach;


            foreach ($arr as $maila):

                foreach($booktitle as $ysa):
                   if(strcasecmp($maila, $ysa)==0){
                         $points[$row->book_no] += 100;
                    }
                   
                endforeach;
                foreach($descr as $ysa1):
                    if(strcasecmp($maila, $ysa1)==0){
                          $points[$row->book_no] += 10;
                    }
                endforeach;
                foreach($tg as $ysa2):
                    if(strcasecmp($maila, $ysa2)==0){
                         $points[$row->book_no] += 1;
                    }
                endforeach;
            endforeach;



            // echo " " . $row->book_no . " " . $points[$row->book_no] . "<br/>";


        endforeach;



        $counter = 0;
        foreach ($points as $key => $value):
                $table[$counter++] = $table_copy[$key];
            // echo $key;
        endforeach;
            //compute points
                //parse search term, programatically compare to book title, desc and tags
                //$sorted[book_no] = $row1
                //$points[book_no] = points
            //sort
            //printing


        return $table;

    }

}

/* End of file booker.php */
/* Location: ./application/controllers/booker.php */