<?php
class CPage
{
	private  $total = 0;
	private $pageSize = 20;
	private $page = 0;
	
	public function __construct($total,$pageSize,$page)
	{
		$this->total=(int)$total;
		$this->pageSize = (int)$pageSize;
		$this->page = (int)$page;
	}
	
	private function buildQuery()
	{
		unset($_GET['page']);
		$queryString = '';
		foreach($_GET as $key=>$value)
		{
			if($queryString == '')
				$queryString.= $key."=".urlencode($value);
			else
				$queryString.= '&'.$key."=".urlencode($value);
		}
		return $queryString;
	}
	
	public function getPageNav()
	{
		if($this->total%$this->pageSize == 0)
		{
			$pageCount = (int)($this->total/$this->pageSize);
		}
		else 
		{
			$pageCount = (int)($this->total/$this->pageSize)+1;
		}

		
		if($this->page > $pageCount)
		{
			$this->page = $pageCount;
		}
		
		if($this->page < 1)
		{
			$this->page = 1;
		}
		
		$queryString = $this->buildQuery();
		$start = ((int)($this->page/10)*10);
		
		$start = $start - 1;
		
		if($start < 0) $start = 1;
		
		$end = $start + 12;

		if($end > $pageCount) $end = $pageCount;

		$output = "&nbsp;&nbsp;&nbsp;<a href='?".$queryString."&page=1'> |<< </a>";
		$output .= "&nbsp;&nbsp;&nbsp;<a href='?".$queryString."&page=".($this->page-1)."'> << </a>";
		
		for($i=$start;$i<=$end;$i++)
		{
			if($i == $this->page)
			{
				$output.= "&nbsp;&nbsp;&nbsp;<a href='?".$queryString."&page=".$i."'><strong style='color:red'> ".$i." </strong></a>";
			}
			else 
			{
				$output.= "&nbsp;&nbsp;&nbsp;<a href='?".$queryString."&page=".$i."'> ".$i." </a>";
			}
		}
		$output .= "&nbsp;&nbsp;&nbsp;<a href='?".$queryString."&page=".($this->page+1)."'> >> </a>";
		$output .= "&nbsp;&nbsp;&nbsp;<a href='?".$queryString."&page=".$pageCount."'> >>| </a>";
		$output .= "&nbsp;第 <form style='margin:0px;display: inline' method='post' enctype='application/x-www-form-urlencoded' action='?".$queryString."'><input type='text' id='page' name='page' size='3'/>&nbsp;&nbsp;<input type='submit' value='GO' /></form>";
		$output.="&nbsp;&nbsp;&nbsp;共".$pageCount."页,".$this->total."条记录";
		return $output;
	}
	
}