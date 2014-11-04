<?php
class Transaction {

	public $id; // transaction id
	public $seller_id; // seller user id
	public $seller; // seller email
	public $buyer_id; // buyer user id
	public $buyer; // buyer email
	public $note_id; // note id
	public $note; // note object
	public $purchased; // purchase date
	public $retail_cost; // full retail cost
	public $payout_amount; // amount paid to seller
	public $currency; // currency paid
	public $completed; // has this transaction been completed or not (boolean)

	/**
	* Construct method
	*
	* @param $arg array
	* Values used to create object
	*/
	function __construct($arg = array()) {
		if (isset($arg['id'])) {
			$this->id = $arg['id'];
			// load rest of data from db
			$this->load();
		}
	}

	/**
	 * Load transaction info
	 */
	public function load(){
		$q = 'SELECT t.id, t.buyer buyer_id, t.seller seller_id, t.note, t.purchased, t.retail_cost, t.payout_amount, t.currency, t.completed, buyer.email buyer_email, seller.email seller_email
			  FROM transactions t
			  LEFT JOIN users buyer on t.buyer = buyer.id
			  LEFT JOIN users seller on t.seller = seller.id
			  WHERE t.id = $1';
		$result = pg_query_params($q, array($this->id));
		$row = pg_fetch_object($result);
		if (!empty($row)) {
			$this->seller_id = $row->seller_id;
			$this->seller = $row->seller_email;
			$this->buyer_id = $row->buyer_id;
			$this->buyer = $row->buyer_email;
			$this->note_id = $row->note;
			$this->note = DB::get_note($this->note_id);
			$this->purchased = $row->purchased;
			$this->retail_cost = $this->retail_cost;
			$this->payout_amount = $this->payout_amount;
			$this->currency = $this->currency;
			$this->completed = $this->completed;
		} else {
			$this->id = NULL;
		}
	}

	/**
	 * Complete a transaction
	 */
	public static function complete($id){
		$q = 'update transactions set completed = TRUE where id = $1';
		$result = pg_query_params($q, array($id));
	}
}