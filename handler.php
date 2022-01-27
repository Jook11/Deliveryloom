<?
namespace LOOMMyDelivery;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale;
use Bitrix\Catalog;
use Bitrix\Iblock;
use Bitrix\Main\Error;




class JinfLoomHandler extends \Bitrix\Sale\Delivery\Services\Base
{

    public static function getClassTitle()
        {
            return 'Доставка по порогам и особеностям Бренда';
        }
        
    public static function getClassDescription()
        {
            return 'три варианта расчета без бренда, с брендом, совместно с брендом ';
        }
        
    protected function calculateConcrete(\Bitrix\Sale\Shipment $shipment)
        {
        $conf=$this->config["MAIN"];
        $idbrend=$conf["LOMM_ID"];
		$loom=0;$drugie=0;$price=0;
        foreach($shipment->getShipmentItemCollection()->getShippableItems() as $shipmentItem)
        {

            $basketItem = $shipmentItem->getBasketItem();
			$price=($basketItem->getPrice()*$basketItem->getQuantity())+$price;
			$itemFieldValues = $basketItem->getFieldValues();
			$prid = $shipmentItem->getProductId();
			//AddMessage2Log(print_r ($itemFieldValues,true), 0 , 0);
			//flag brend

			$dbItems = \Bitrix\Iblock\ElementTable::getList(array('select' => array('ID', 'NAME', 'IBLOCK_ID', "PROPERTY_*"),'filter' => array("ID"=>$itemFieldValues['PRODUCT_ID']),	'limit' => 1))->fetchAll();
			foreach ($dbItems as $key=>$product) {
				if($product['IBLOCK_ID']==5){
					$dbProperty = \CIBlockElement::getProperty($product['IBLOCK_ID'], $product['ID'], array("sort", "asc"), array('CODE' => 'CML2_LINK'));
					while ($arProperty = $dbProperty->GetNext()) {
						if ($arProperty['VALUE']) {
							$dbItems[$key]['CML2_LINK'] = $arProperty['VALUE'];
						}
					}
					if($dbItems[$key]['CML2_LINK']>0){
						$dbItems4 = \Bitrix\Iblock\ElementTable::getList(array('filter' => array("ID"=>$dbItems[$key]['CML2_LINK']),	'limit' => 1));
						while ($itm4 = $dbItems4->fetch()) {
							$dbProperty = \CIBlockElement::getProperty($itm4['IBLOCK_ID'], $itm4['ID'], array("sort", "asc"), array('CODE' => 'MANUFACTURE'));
							while ($arProperty = $dbProperty->GetNext()) {
								if($arProperty['VALUE']){
									if($arProperty['VALUE']==$idbrend){
										$loom=1;
									}
									else{
										$drugie=1;
									}
									$dbItems[$key]['BRAND'][] = $arProperty['VALUE'];
								}
							}
						}
					}
				}
				elseif($product['IBLOCK_ID']==4){
					$dbProperty = \CIBlockElement::getProperty($product['IBLOCK_ID'], $product['ID'], array("sort", "asc"), array('CODE' => 'MANUFACTURE'));
					while ($arProperty = $dbProperty->GetNext()) {
						if($arProperty['VALUE']){
							if($arProperty['VALUE']==$idbrend){
								$loom=1;
							}
							else{
								$drugie=1;
							}
							$dbItems[$key]['BRAND'][] = $arProperty['VALUE'];
						}
					}
				}
			$aritm[]=$dbItems;
			}
		}

		$result = new CalculationResult();
		
		//только бренд
		//$loom=0;//отключаем бренд
		if($loom==1 && $drugie<>1){

			$per='LOMM_';
			$pr=(int)$conf[$per.'POR'];
			$pr1=(int)$conf[$per.'POR1'];
			$pr2=(int)$conf[$per.'POR2'];
			$pr3=(int)$conf[$per.'POR3'];

			if($price < $pr && $pr>0){
				$result->addError(new \Bitrix\Main\Error("Ошибка доставки: ".$conf[$per.'MESS']));
			}
			elseif( $price < $pr1 && $pr1>0){
				$result->setDeliveryPrice(roundEx($conf[$per.'PR1'], 2));
				if(strlen($conf[$per.'MESS1'])>0){
					$result->setPeriodDescription($conf[$per.'MESS1']);
				}
			}
			elseif($price < $pr2 && $pr2>0){
				$result->setDeliveryPrice(roundEx($conf[$per.'PR2'], 2));
				if(strlen($conf[$per.'MESS2'])>0){
					$result->setPeriodDescription($conf[$per.'MESS2']);
				}
			}
			elseif($price < $pr3 && $pr3>0){
				$result->setDeliveryPrice(roundEx($conf[$per.'PR3'], 2));
				if(strlen($conf[$per.'MESS3'])>0){
					$result->setPeriodDescription($conf[$per.'MESS3']);
				}
			}
			//AddMessage2Log(print_r (array($price),true), 0 , 0);
			//AddMessage2Log(print_r (array($conf),true), 0 , 0);

		}
		//c brend
		elseif($loom==1){
			$per='S_LOMM_';
			$pr=(int)$conf[$per.'POR'];
			$pr1=(int)$conf[$per.'POR1'];
			$pr2=(int)$conf[$per.'POR2'];
			$pr3=(int)$conf[$per.'POR3'];

			if($price < $pr && $pr>0){
				$result->addError(new \Bitrix\Main\Error("Ошибка доставки: ".$conf[$per.'MESS']));
			}
			elseif( $price < $pr1 && $pr1>0){
				$result->setDeliveryPrice(roundEx($conf[$per.'PR1'], 2));
				if(strlen($conf[$per.'MESS1'])>0){
					$result->setPeriodDescription($conf[$per.'MESS1']);
				}
			}
			elseif($price < $pr2 && $pr2>0){
				$result->setDeliveryPrice(roundEx($conf[$per.'PR2'], 2));
				if(strlen($conf[$per.'MESS2'])>0){
					$result->setPeriodDescription($conf[$per.'MESS2']);
				}
			}
			elseif($price < $pr3 && $pr3>0){
				$result->setDeliveryPrice(roundEx($conf[$per.'PR3'], 2));
				if(strlen($conf[$per.'MESS3'])>0){
					$result->setPeriodDescription($conf[$per.'MESS3']);
				}
			}
		}
		//обычная
		else{
			$per='NO_LOMM_';
			$pr=(int)$conf[$per.'POR'];
			$pr1=(int)$conf[$per.'POR1'];
			$pr2=(int)$conf[$per.'POR2'];
			$pr3=(int)$conf[$per.'POR3'];

			if($price < $pr && $pr>0){
				$result->addError(new \Bitrix\Main\Error("Ошибка доставки: ".$conf[$per.'MESS']));
			}
			elseif( $price < $pr1 && $pr1>0){
				$result->setDeliveryPrice(roundEx($conf[$per.'PR1'], 2));
				if(strlen($conf[$per.'MESS1'])>0){
					$result->setPeriodDescription($conf[$per.'MESS1']);
				}
			}
			elseif($price < $pr2 && $pr2>0){
				$result->setDeliveryPrice(roundEx($conf[$per.'PR2'], 2));
				if(strlen($conf[$per.'MESS2'])>0){
					$result->setPeriodDescription($conf[$per.'MESS2']);
				}
			}
			elseif($price < $pr3 && $pr3>0){
				$result->setDeliveryPrice(roundEx($conf[$per.'PR3'], 2));
				if(strlen($conf[$per.'MESS3'])>0){
					$result->setPeriodDescription($conf[$per.'MESS3']);
				}
			}

		}

			//AddMessage2Log(print_r (array($price),true), 0 , 0);

			//AddMessage2Log(print_r (array($pr1),true), 0 , 0);
            return $result;
        }

    protected function getConfigStructure()
        {
			$result["MAIN"] = array("TITLE" => 'Настройка обработчика',"DESCRIPTION" => 'Настройка обработчика');

        $result['MAIN']['ITEMS']["DEFAULT_VALUES"] = array(
            "TYPE" => "DELIVERY_SECTION",
            "NAME" =>"настройка расчета только бренда",
        );
    $result['MAIN']['ITEMS']['LOMM_ID']=array("TYPE" => "STRING","NAME" => 'ID Бренда');
		$result['MAIN']['ITEMS']['LOMM_MESS']=array("TYPE" => "STRING","NAME" => 'Сообщение если меньше');
		$result['MAIN']['ITEMS']['LOMM_POR']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Порог первый с');
 		$result['MAIN']['ITEMS']['LOMM_POR1']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Порог первый до');
 		$result['MAIN']['ITEMS']['LOMM_PR1']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Цена порога');
 		$result['MAIN']['ITEMS']['LOMM_MESS1']=array("TYPE" => "STRING","NAME" => 'Сообщение порога');
 		$result['MAIN']['ITEMS']['LOMM_POR2']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Порог 2 до');
 		$result['MAIN']['ITEMS']['LOMM_PR2']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Цена порога');
		$result['MAIN']['ITEMS']['LOMM_MESS2']=array("TYPE" => "STRING","NAME" => 'Сообщение порога');
 		$result['MAIN']['ITEMS']['LOMM_POR3']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Порог 3 до');
 		$result['MAIN']['ITEMS']['LOMM_PR3']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Цена порога');
		$result['MAIN']['ITEMS']['LOMM_MESS3']=array("TYPE" => "STRING","NAME" => 'Сообщение порога');

        $result['MAIN']['ITEMS']["DEFAULT_VALUES2"] = array(
            "TYPE" => "DELIVERY_SECTION",
            "NAME" =>"настройка расчета c брендом",
        );
		$result['MAIN']['ITEMS']['S_LOMM_MESS']=array("TYPE" => "STRING","NAME" => 'Сообщение если меньше');
		$result['MAIN']['ITEMS']['S_LOMM_POR']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Порог первый с');
 		$result['MAIN']['ITEMS']['S_LOMM_POR1']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Порог первый до');
 		$result['MAIN']['ITEMS']['S_LOMM_PR1']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Цена порога');
		$result['MAIN']['ITEMS']['S_LOMM_MESS1']=array("TYPE" => "STRING","NAME" => 'Сообщение порога');
 		$result['MAIN']['ITEMS']['S_LOMM_POR2']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Порог 2 до');
 		$result['MAIN']['ITEMS']['S_LOMM_PR2']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Цена порога');
		$result['MAIN']['ITEMS']['S_LOMM_MESS2']=array("TYPE" => "STRING","NAME" => 'Сообщение порога');
 		$result['MAIN']['ITEMS']['S_LOMM_POR3']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Порог 3 до');
 		$result['MAIN']['ITEMS']['S_LOMM_PR3']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Цена порога');
		$result['MAIN']['ITEMS']['S_LOMM_MESS3']=array("TYPE" => "STRING","NAME" => 'Сообщение порога');

        $result['MAIN']['ITEMS']["DEFAULT_VALUES3"] = array(
            "TYPE" => "DELIVERY_SECTION",
            "NAME" =>"настройка расчета без бренда",
        );
		$result['MAIN']['ITEMS']['NO_LOMM_MESS']=array("TYPE" => "STRING","NAME" => 'Сообщение если меньше');
		$result['MAIN']['ITEMS']['NO_LOMM_POR']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Порог первый с');
 		$result['MAIN']['ITEMS']['NO_LOMM_POR1']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Порог первый до');
 		$result['MAIN']['ITEMS']['NO_LOMM_PR1']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Цена порога');
		$result['MAIN']['ITEMS']['NO_LOMM_MESS1']=array("TYPE" => "STRING","NAME" => 'Сообщение порога');
 		$result['MAIN']['ITEMS']['NO_LOMM_POR2']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Порог 2 до');
 		$result['MAIN']['ITEMS']['NO_LOMM_PR2']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Цена порога');
		$result['MAIN']['ITEMS']['NO_LOMM_MESS2']=array("TYPE" => "STRING","NAME" => 'Сообщение порога');
 		$result['MAIN']['ITEMS']['NO_LOMM_POR3']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Порог 3 до');
 		$result['MAIN']['ITEMS']['NO_LOMM_PR3']=array("TYPE" => "NUMBER","MIN" => 0,"NAME" => 'Цена порога');
		$result['MAIN']['ITEMS']['NO_LOMM_MESS3']=array("TYPE" => "STRING","NAME" => 'Сообщение порога');
            return $result;
        }

    public function isCalculatePriceImmediately()
        {
            return true;
        }
        
    public static function whetherAdminExtraServicesShow()
        {
            return true;
        }
}
?>
