<?php
class CIndexController extends CBaseController
{

    const RECOMMENDATION_DEFAULT_LIMIT = 10;
    const LIST_PAGE_LIMIT = 50;

	public function indexAction()
	{
		$this->render('index/index.html');
	}
	
	public function listAction()
	{
		$offset =(int) ($_GET['offset'] ?: 0);
        $limit = CIndexController::LIST_PAGE_LIMIT;

        $params = $this->extractQueryParams();
		
		$products = $this->queryProducts($params, $offset, $limit);
		
		$this->data['products'] = $products;
		$this->data['limit'] = $limit;
        $this->data['offset'] = $offset;
        $this->data['category'] = CProduct::getCategories(10);
        $this->data['brand'] = CProduct::getBrands(10);
        $this->data['color'] = CProduct::getColors(10);
        $this->data['size'] = CProduct::getSizes(5);

		$this->render('index/list.php', true);

	}
	
	public function detailAction()
	{
		$id = (int)$_GET['id'];
		$product = CProduct::model()->get($id);
		
		if($product == null)
		{
			throw new Exception("product id can not found", 404);
		}
		
		$images = explode("|", $product->getDetailImages());
		$product->setDetailImages($images);

		$this->data['product'] = $product;
        $this->data['recommendation'] =
            $this->recommend($id, CIndexController::RECOMMENDATION_DEFAULT_LIMIT);

		$this->render('index/detail.php', true);
	}
	
	public function outputAction()
	{
        $category = CProduct::getColors(10);
        var_dump($category);
	}

    private function extractQueryParams()
    {
        $params = array();
        $expected = array("color", "size", "brand", "category");

        foreach ($expected as $column)
        {
            // TODO: check and clean values got
            if (!empty($_GET[$column])) {
                $params[$column] = $_GET[$column];
            }
        }

        return $params;

    }

    private function recommend($id, $limit)
    {
        // TODO, real recommendation
        $product = CProduct::model()->get($id);
        $condition = sprintf("WHERE brand = \"%s\" ORDER BY RAND() LIMIT %d",
            $product->getBrand(), (int)$limit);
        $recommended_products = CProduct::query($condition);
        return $recommended_products;
    }

    private function queryProducts($params, $offset, $limit)
    {

        $joiner = function($k, $v)
        {
            return $k . '="' . $v . '"';
        };

        $condition = join(" AND ", array_map($joiner, array_keys($params), $params));
        $condition = !empty($condition) ? "WHERE " . $condition : "";
        return CProduct::query(
            sprintf("%s ORDER BY id LIMIT %d, %d", $condition, $offset, $limit)
        );

    }

}