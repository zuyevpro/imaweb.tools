<?
namespace Imaweb\Tools;

use Bitrix\Main\Loader;

class CartController
{
    private $sessionKey = 'WEBSITE_CART';
    private $cart = array();

    private $el = null;
    private $iblockId = -1;

    private static $_instance;
    public static function getInstance()
    {
        if (is_null(self::$_instance))
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * CartController constructor.
     */
    public function __construct()
    {
        Loader::includeModule('iblock');

        $this->el = new \CIBlockElement();
        $this->iblockId = constant('IBLOCK_CATALOG_CATALOG');

        $this->_fromSession();
    }

    private function _fromSession()
    {
        if (!is_array($_SESSION[$this->sessionKey]))
        {
            $_SESSION[$this->sessionKey] = array();
        }

        $this->cart = $_SESSION[$this->sessionKey];
    }

    private function _toSession()
    {
        $_SESSION[$this->sessionKey] = $this->cart;
    }

    public function add($productId, $quantity = 1)
    {
        if ($productId <= 0)
        {
            return false;
        }

        if ($quantity <= 0)
        {
            return false;
        }

        foreach ($this->cart as $key => $cartItem)
        {
            if ($cartItem['id'] == $productId)
            {
                $this->cart[$key]['quantity'] += $quantity;
                $this->_toSession();
                return true;
            }
        }

        $res = $this->el->GetList(array(), array(
            'IBLOCK_ID' => $this->iblockId,
            'ACTIVE' => 'Y',
            '=ID' => $productId,
        ), false, false, array(
            'IBLOCK_ID',
            'ID',
            'NAME',
            'PROPERTY_PRICE',
	        'PROPERTY_DIMENSION',
        ));

        if ($r = $res->GetNext())
        {
            $this->cart[] = array(
                'id' => $r['ID'],
                'name' => $r['NAME'],
                'quantity' => $quantity,
                'price' => $r['PROPERTY_PRICE_VALUE'],
	            'dimension' => $r['PROPERTY_DIMENSION_VALUE'],
            );

            $this->_toSession();
            return true;
        }

        return false;
    }

    public function update($productId, $quantity = 1)
    {
        if ($productId <= 0)
        {
            return false;
        }

        if ($quantity <= 0)
        {
            return false;
        }

        foreach ($this->cart as $key => $cartItem)
        {
            if ($cartItem['id'] == $productId)
            {
                $this->cart[$key]['quantity'] = $quantity;
                $this->_toSession();
                return true;
            }
        }

        return false;
    }

    public function remove($productId)
    {
        if ($productId <= 0)
        {
            return false;
        }

        foreach ($this->cart as $key => $cartItem)
        {
            if ($cartItem['id'] == $productId)
            {
                unset($this->cart[$key]);
                $this->_toSession();
                return true;
            }
        }

        return false;
    }

    public function clear()
    {
    	$this->cart = array();
    	$this->_toSession();
    }

    public function items()
    {
        return $this->cart;
    }

    public function count()
    {
        return count($this->cart);
    }
}