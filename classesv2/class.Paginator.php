<?php

class Paginator{
	var $items_per_page;
	var $items_total;
	var $current_page;
	var $num_pages;
	var $mid_range = 3;
	var $low;
	public $high;
	var $limit;
	var $return;
	var $default_ipp = 25;
	var $querystring;
	var $thispage;
	var $ipparray;
	private $_newviewfirst;

	function Paginator($myDbConfig,$newviewfirst = False, $view = "table")
	{
		$this->current_page = 1;
		$this->thispage = $myDbConfig['mainpage'];
		$this->ipparray = $myDbConfig['ipparray'][$view];
		$this->_newviewfirst = $newviewfirst;
	}

	public function setItemsPerPage($previpp = 25)
	{
		if (!empty ($_GET['ipp'])) {
			if ($_GET['ipp'] > max($this->ipparray)) $_GET['ipp'] = max($this->ipparray);
			$this->items_per_page = $_GET['ipp'];
		} else {
			if ($previpp > max($this->ipparray)) $previpp = max($this->ipparray);
			$this->items_per_page = $previpp;
			$_GET['ipp'] = $previpp;
		}

		return $this->items_per_page;
	}

	function paginate()
	{
		if(!is_numeric($this->items_per_page) OR $this->items_per_page <= 0) $this->items_per_page = $this->default_ipp;
		$this->num_pages = ceil($this->items_total/$this->items_per_page);
		$this->current_page = $this->_newviewfirst ?  (int) $this->_newviewfirst : 1; // must be numeric > 0
		if($this->current_page < 1 Or !is_numeric($this->current_page)) $this->current_page = 1;
		if($this->current_page > $this->num_pages + 1) $this->current_page = $this->num_pages + 1;
		$prev_page = $this->current_page-1;
		$next_page = $this->current_page+1;


		if($this->num_pages > 4)
		{
			$this->return = ($this->current_page != 1 And $this->items_total >= 4) ? "<a class=\"paginate\" href=\"$_SERVER[PHP_SELF]?viewfirst=$prev_page&ipp=$this->items_per_page$this->querystring\">&laquo; Previous</a> ":"<span class=\"inactive\" href=\"#\">&laquo; Previous</span> ";

			$this->start_range = $this->current_page - floor($this->mid_range/2);
			$this->end_range = $this->current_page + floor($this->mid_range/2);

			if($this->start_range <= 0)
			{
				$this->end_range += abs($this->start_range)+1;
				$this->start_range = 1;
			}
			if($this->end_range > $this->num_pages)
			{
				$this->start_range -= $this->end_range-$this->num_pages;
				$this->end_range = $this->num_pages;
			}
			$this->range = range($this->start_range,$this->end_range);

			for($i=1;$i<=$this->num_pages;$i++)
			{
				if($this->range[0] > 2 And $i == $this->range[0]) $this->return .= " ... ";
				// loop through all pages. if first, last, or in range, display
				if($i==1 Or $i==$this->num_pages Or in_array($i,$this->range))
				{
					$this->return .= ($i == $this->current_page) ? "<a title=\"Go to page $i of $this->num_pages\" class=\"current\" href=\"#\">$i</a> ":"<a class=\"paginate\" title=\"Go to page $i of $this->num_pages\" href=\"$this->thispage?viewfirst=$i&ipp=$this->items_per_page\">$i</a> ";
				}
				if($this->range[$this->mid_range-1] < $this->num_pages-1 And $i == $this->range[$this->mid_range-1]) $this->return .= " ... ";
			}
			$this->return .= (($this->current_page != $this->num_pages And $this->items_total >= 4)) ? "<a class=\"paginate\" href=\"$this->thispage?viewfirst=$next_page&ipp=$this->items_per_page\">Next &raquo;</a>\n":"<span class=\"inactive\" href=\"#\">&raquo; Next</span>\n";
		}
		else
		{
			for($i=1;$i<=$this->num_pages;$i++)
			{
				$this->return .= ($i == $this->current_page) ? "<a class=\"current\" href=\"#\">$i</a> ":"<a class=\"paginate\" href=\"$this->thispage?viewfirst=$i&ipp=$this->items_per_page\">$i</a> ";
			}
		}
		$this->low = ($this->current_page-1) * $this->items_per_page;

		$this->high = $this->current_page * $this->items_per_page-1;
		$this->limit = " LIMIT $this->low,$this->items_per_page";
	}

	function display_items_per_page()
	{
		$items = '';
		$ipp_array = $this->ipparray;
		foreach($ipp_array as $ipp_opt)	$items .= ($ipp_opt == $this->items_per_page) ? "<option selected value=\"$ipp_opt\">$ipp_opt</option>\n":"<option value=\"$ipp_opt\">$ipp_opt</option>\n";
		return "<span class=\"paginate\">Items per page:</span><select class=\"paginate\" onchange=\"window.location='$this->thispage?viewfirst=1&ipp='+this[this.selectedIndex].value+'';return false\">$items</select>\n";
	}

	function display_jump_menu()
	{
		$option = "";
		for($i=1;$i<=$this->num_pages;$i++)
		{
			$option .= ($i==$this->current_page) ? "<option value=\"$i\" selected>$i</option>\n":"<option value=\"$i\">$i</option>\n";
		}
		return "<span class=\"paginate\">Page:</span><select class=\"paginate\" onchange=\"window.location='$this->thispage?viewfirst='+this[this.selectedIndex].value+'&ipp=$this->items_per_page';return false\">$option</select>\n";
	}

	function display_pages()
	{
		return $this->return;
	}
}