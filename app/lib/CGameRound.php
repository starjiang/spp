<?php
class CGameRound
{
	private $data = array();
	private $memcached = null;
	
	public function __construct()
	{
		$this->memcached = CConnMgr::init()->getMem($cfg);
	}
	
	public function init($players,$top,$max,$max_follow)
	{
		$this->data = array(
				'roundId'=>'xxxx',
				'all' => 0,
				'curr_bid' => 0,
				'top'=>$top,
				'max'=>$max,
				'max_follow'=>$max_follow,					
		);
		
		foreach ($players as $player)
		{
			$this->data[$player]['pokers'] = array();
			$this->data[$player]['is_open'] = false;
			$this->data[$player]['is_giveup'] = false;
			$this->data[$player]['coins'] = 0;
			$this->data[$player]['follows'] = 0;
		}
		$this->save();
	}
	
	public function get($roundId)
	{
		$data = $this->memcached->get($roundId);
		$this->data = json_decode($data);
		return this;
	}
	
	public function save()
	{
		return $this->memcached->set($this->data['roundId'],json_encode($this->data));
	}
	
	public function delete($roundId)
	{
		return $this->memcached->delete($roundId);
	}
	
	public function open($player)
	{
		$this->data[$player]['is_open'] = true;
		$user = CMGUser::model()->get($player);
		$user->setCoins($user->getCoins()-100);
	}
	
	public function follow($player)
	{
		
	}
	public function bid($player)
	{
		
	}
	
	public function giveUp($player)
	{
		
	}
	
	public function vs($player1,$player2)
	{
		
	}
	
	public function checkOut()
	{
		
	}
	
	public static function model()
	{
		return new CGameRound();
	}
}