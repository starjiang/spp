<?php
class CProduct extends CDBModel
{
	protected static $fields = array('id'=>0,'name'=>'','brand'=>'','price'=>0,'style'=>'','category'=>'',
									'color'=>'','detail_images'=>'','thumb_images'=>'','shop_url'=>'',
									);
	private static $pdo = null;
	protected static $cfg = null;

    private static $GET_DISTINCT_QUERY_PAT =
        'SELECT DISTINCT %s FROM product WHERE %s IS NOT NULL AND %s != "" GROUP BY %s ORDER BY COUNT(%s) DESC LIMIT %d';
	
	public function __construct()
	{
        $this->__init__();

	}

    private static function __init__()
    {
        if(self::$cfg == null)
        {
            self::$cfg = CCReader::get('cfg.services.db.'.get_called_class());
        }

        if(self::$pdo == null)
        {
            self::$pdo = CConnMgr::init()->pdo(self::$cfg);
        }

    }

	protected  function prefix()
	{
		return self::$cfg['prefix'];
	}
	
	protected function pdo()
	{
		if(self::$pdo == null)
		{
			self::$pdo = CConnMgr::init()->pdo(self::$cfg);
		}
		return self::$pdo;
	}

    public static function getCategories($limit)
    {

        //TODO move to autoloading
        CProduct::__init__();

        $query = self::$pdo->prepare(CProduct::constructQuery("category", $limit));
        return ($query->execute() === true) ? $query->fetchAll(PDO::FETCH_COLUMN) : array();

    }

    public static function getColors($limit)
    {
        CProduct::__init__();

        $query = self::$pdo->prepare(CProduct::constructQuery("color", $limit));
        return ($query->execute() === true) ? $query->fetchAll(PDO::FETCH_COLUMN) : array();

    }

    public static function getBrands($limit)
    {
        CProduct::__init__();

        $query = self::$pdo->prepare(CProduct::constructQuery("brand", $limit));
        return ($query->execute() === true) ? $query->fetchAll(PDO::FETCH_COLUMN) : array();

    }

    public static function getSizes($limit)
    {
        CProduct::__init__();

        $query = self::$pdo->prepare(CProduct::constructQuery("size", $limit));
        return ($query->execute() === true) ? $query->fetchAll(PDO::FETCH_COLUMN) : array();

    }

    private static function constructQuery($column, $limit)
    {
        return sprintf(CProduct::$GET_DISTINCT_QUERY_PAT,
            $column, $column, $column, $column, $column, $limit);
    }
	
}