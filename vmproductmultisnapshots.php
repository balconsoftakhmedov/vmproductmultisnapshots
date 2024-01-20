<?php
/**
 * @copyright    Copyright (C) 2012 Flance.info. All rights reserved
 * @license        GNU General Public License version 2 or later;
 */

// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;

jimport('joomla.plugin.plugin');


class plgContentVmproductmultisnapshots extends JPlugin
{
	protected static $modules = array();
	protected static $mods = array();

	public $searchword = '';
	public $params = [];

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param string    The context of the content being passed to the plugin.
	 * @param object    The article object.  Note $article->text is also available
	 * @param object    The article params
	 * @param int        The 'page' number
	 */
public function get_products(){
		echo 'teee';
	}
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	public function init()
	{

		if (!class_exists('VmConfig')) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php');
		VmConfig::loadConfig();
		// Load the language file of com_virtuemart.
		JFactory::getLanguage()->load('com_virtuemart');
		if (!class_exists('calculationHelper')) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'calculationh.php');
		if (!class_exists('CurrencyDisplay')) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'currencydisplay.php');
		if (!class_exists('VirtueMartModelVendor')) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models' . DS . 'vendor.php');
		if (!class_exists('VmImage')) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'image.php');
		if (!class_exists('shopFunctionsF')) require(JPATH_SITE . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'shopfunctionsf.php');

		if (!class_exists('calculationHelper')) require_once(JPATH_SITE . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'cart.php');
		if (!class_exists('VirtueMartCart')) require_once(JPATH_SITE . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'cart.php');

		if (!class_exists('VirtueMartModelProduct')) {
			JLoader::import('product', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models');
		}

		vmJsApi::jPrice();
		vmJsApi::jQuery();
		vmJsApi::cssSite();

	}

	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{


		// simple performance check to determine whether bot should process further
		if (strpos($article->text, 'product_multi_snapshot') === false) {
			return true;
		}
		$param_defaults = array(
			'id' => '0',
			'showname' => 'y',
			'showimage' => 'y',
			'showdesc' => 'y',
			'showsku' => 'n',
			'showpkg' => 'n',
			'showprice' => 'y',
			'quantity' => '0',
			'showaddtocart' => 'y',
			'displaylist' => 'v',
			'displayeach' => 'h',
			'width' => '100',
			'border' => '0',
			'styling' => '',
			'align' => '',
			'search' => 'no'
		);
		// get settings from admin mambot parameters
		foreach ($param_defaults as $key => $value) {
			$param_defaults[$key] = $this->params->def($key, $value);
		}

		// expression to search for (positions)
		$regex = '/{product_multi_snapshot:.+?}/i';


		$vm_productsnap_entrytext = $article->text;
		$vm_productsnap_matches = array();
		if (preg_match_all("/{product_multi_snapshot:.+?}/", $vm_productsnap_entrytext, $vm_productsnap_matches, PREG_PATTERN_ORDER) > 0) {
			$i = 0;
			foreach ($vm_productsnap_matches[0] as $vm_productsnap_match) {
				$vm_productsnap_match = str_replace("{product_multi_snapshot:", "", $vm_productsnap_match);
				$vm_productsnap_match = str_replace("}", "", $vm_productsnap_match);

				// Get Bot Parameters
				$vm_productsnap_params = $this->get_prodsnap_params($vm_productsnap_match, $param_defaults);

				// Get the html
				$showsnapshot = $this->return_snapshot($vm_productsnap_params, 'multiproduct' . $i);


				$vm_productsnap_entrytext = preg_replace("/{product_multi_snapshot:.+?}/", $showsnapshot, $vm_productsnap_entrytext, 1);
				// $vm_productsnap_entrytext = $showsnapshot ;

				$i++;
			}
			$article->text = $vm_productsnap_entrytext;

		}
		return;


	}

	public function get_nav_list_html($nav, $categs)
	{
		ob_start();
		$objJURI = $objJURI = JFactory::getURI();
		$strQuery = '?';
		if (empty($objJURI->getQuery())) {
			$strQuery = '?';
		}
		$currentURL = $objJURI->current() . $strQuery;

		$cat_name = ($_GET['cat_id']) ? filter_var($_GET['cat_id'], FILTER_SANITIZE_STRING) : $categs[0]['cat_name'];
		//  $array_key = array_search ($cat_name, $categs );
		// if ((in_array( $array_key, $searched_categories)) || empty($searched_categories)):
		$searchword = ($_REQUEST['searchword']) ? "&searchword={$_REQUEST['searchword']}" : "";
		?>

        <div class="vm-pagination">
            <ul class="pagination">

				<?php

				$nav_selected = (empty($_REQUEST['navpage'])) ? '1' : $_REQUEST['navpage'];
				foreach ($nav as $nav_key => $nav_val) {
					$nav_selected = (empty($_REQUEST['navpage'])) ? '1' : $nav_key;
					$selected = (($_REQUEST['navpage'] == $nav_selected) ||
						((empty($_REQUEST['navpage']) && $nav_selected == '1' && $nav_key == $nav_selected))) ? 'active' : '';
					?>
                    <li class=" <?php echo $selected ?>">
                        <a class="pagenav"
                           href="<?php echo $currentURL; ?>cat_id=<?php echo $cat_name ?>&navpage=<?php echo $nav_key ?><?php echo $searchword ?>"><?php echo $nav_key ?></a>

                    </li>
					<?php
				}
				?>
            </ul>
            <span style="float:right">Page <?php echo $nav_selected ?> of  <?php echo $nav_key ?></span>
        </div>

		<?php
		return ob_get_clean();
	}

	public function get_cat_list_html($categs)
	{
		ob_start();
		$objJURI = $objJURI = JFactory::getURI();;
		$strQuery = '';
		$strQuery = '?';
		if (empty($objJURI->getQuery())) {
			$strQuery = '?';
		}
		$currentURL = $objJURI->current() . $strQuery;
		$searchword = ($_REQUEST['searchword']) ? "&searchword={$_REQUEST['searchword']}" : "";
		?>

        <ul class="row">
			<?php
			foreach ($categs as $key => $cat) {
				?>
                <li>
                    <a href="<?php echo $currentURL; ?>cat_id=<?php echo $cat['cat_name'] ?><?php echo $searchword ?>"><?php echo $cat['cat_name'] ?></a>
                    |
                </li>
				<?php
			}
			?>
        </ul>
		<?php
		return ob_get_clean();
	}

	function searh_html()
	{
		$doc = JFactory::getDocument();
		$sutl = JURI::base();
		$stlurl = $sutl . "/plugins/content/vmproductmultisnapshots/js/search.js";
		$doc->addScript($stlurl);

		ob_start(); ?>
        <div class="search">
            <form id="searchForm" action="" method="get">
                <div class="btn-toolbar">
                    <div class="btn-group pull-left">
                        <label for="search-searchword" class="element-invisible">
                            Search Keyword: </label>
                        <input type="text" name="searchword" title="Search Keyword:" placeholder="Search Keyword:"
                               id="search-searchword" size="30" maxlength="200"
                               value="<?php echo $_REQUEST['searchword'] ?>"
                               class="inputbox">
                    </div>
                    <div class="btn-group pull-left">
                        <button type="submit" onclick="" class="btn hasTooltip" title="Search">
                            <span class="icon-search"></span>
                            Search
                        </button>
                    </div>

                    <div class="clearfix"></div>
                </div>
                <div class="searchintro">
                </div>
            </form>
        </div>
		<?php
		return ob_get_clean();
	}

	public function add_css() {
		$document = JFactory::getDocument();
		$sutl   = $hurl = JURI::base();
		$stlurl = $sutl . "plugins/content/vmproductmultisnapshots/assets/style.css";
		$document->addStyleSheet( $stlurl );
		$js_url = $sutl . "plugins/content/vmproductmultisnapshots/assets/js/multi_add.js";
		$document->addScript($js_url);
	}
	public function add_multi_products(){

			$mainframe = JFactory::getApplication();

			$post = JRequest::get('default');
			$product_ids = $post['product_ids'];
			$cart = VirtueMartCart::getCart();
			if ($cart) {

				foreach ($product_ids as $p_key => $virtuemart_product_id) {
					$quantityPost = (int)$post['quantity'][$p_key];
					if ($quantityPost > 0) {
						$virtuemart_product_ids[$p_key] = $virtuemart_product_id;
					}
				}
				$success = true;


				if ($cart->add($virtuemart_product_ids, $success)) {
					$msg = JText::_('COM_VIRTUEMART_PRODUCT_ADDED_SUCCESSFULLY');
					$type = '';
				} else {
					$msg = JText::_('COM_VIRTUEMART_PRODUCT_NOT_ADDED_SUCCESSFULLY');
					$type = 'error';
				}

				$mainframe->enqueueMessage($msg, $type);
				$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart'));

			} else {
				$mainframe->enqueueMessage('Cart does not exist?', 'error');
			}
	}
	function return_snapshot(&$params, $formclass)
	{

		$this->init();

		jimport('joomla.environment.uri');
		$VM_LANG = JFactory::getLanguage();
		$sess = JFactory::getSession();

		$document = JFactory::getDocument();

		$sutl = $hurl = JURI::base();
		$stlurl = $sutl . "/plugins/content/vmproductmultisnapshots/style.css";
		$document->addStyleSheet($stlurl);
		$stlurl = $hurl . "/plugins/content/vmproductmultisnapshots/js/main.js";
		//	$document->addScript($stlurl);


		if ((vRequest::uword('func', '', ' ') == 'multiCartAdd' . $formclass . '') && (vRequest::uword('formating', '', ' ') != 'jsontotal')) {
//echo "<pre>";print_r($_POST);exit;
			$mainframe = JFactory::getApplication();
			//$product_ids = str_replace("'", "", $params['id']);
			//$product_ids = explode(',', $product_ids);
			$post = JRequest::get('default');
			$product_ids = $post['product_ids'];
			$cart = VirtueMartCart::getCart();
			if ($cart) {

				foreach ($product_ids as $p_key => $virtuemart_product_id) {
					$quantityPost = (int)$post['quantity'][$p_key];
					if ($quantityPost > 0) {
						$virtuemart_product_ids[$p_key] = $virtuemart_product_id;
					}
				}
				$success = true;
			//	$post['quantity'] = array_diff($post['quantity'], array(0));
			//	$_POST['quantity'] = $post['quantity'];
			//	$_POST['product_ids'] = $virtuemart_product_ids;
			//	print_r($post['quantity']);				print_r($virtuemart_product_ids); 				echo "test";exit;

				if ($cart->add($virtuemart_product_ids, $success)) {
					$msg = JText::_('COM_VIRTUEMART_PRODUCT_ADDED_SUCCESSFULLY');
					$type = '';
				} else {
					$msg = JText::_('COM_VIRTUEMART_PRODUCT_NOT_ADDED_SUCCESSFULLY');
					$type = 'error';
				}

				$mainframe->enqueueMessage($msg, $type);
				///		$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart'));

			} else {
				$mainframe->enqueueMessage('Cart does not exist?', 'error');
			}


		}

		if ((vRequest::uword('formating', '', ' ') == 'jsontotal')) {
			$mainframe = JFactory::getApplication();
			$product_ids = str_replace("'", "", $params['id']);
			$product_ids = explode(',', $product_ids);

			$post = JRequest::get('default');
			$currency = CurrencyDisplay::getInstance();


			print_r($currency->getSymbol());

			$pricefull['priceformated'] = $currency->createPriceDiv('', '', $post['total'], FALSE);
			$pricefull['pricetax'] = $currency->createPriceDiv('', '', $post['totaltax' . $formclass . ''], FALSE);

// Get the document object.

			echo json_encode($pricefull);
			jexit();
			exit;


		}
		$uri = &JFactory::getURI();
		$session = JFactory::getSession();
		$product_ids = explode(',', $params['id']);
		$productModel = VmModel::getModel('Product');
		$catmodel = VmModel::getModel('category');
		$childcats = $catmodel->getChildCategoryList(1, 0, null, null, true);


		foreach ($childcats as $childcat) {
			$category_title = $catmodel->getCategory($childcat->virtuemart_category_id);
			$categs[] = [
				'cat_id' => $childcat->virtuemart_category_id,
				'cat_name' => $category_title->category_name
			];
		}

		$session->set('shopurl', $uri->toString());
		$html = '';
		$searched_categories = null;
		if ($params['search'] == 'yes') {
			$html .= $this->searh_html();
			$jinput = JFactory::getApplication()->input;
			$this->searchword = $searchword = $jinput->get('searchword', '', 'string');
			//$categs = $this->categories_by_searchword($searchword, $categs);

		}
		$this->params = $params;
		$s = 1;
		$nav = [];
		$limit = VmConfig::get('products_per_row', 3);;
		$customlimit = 700;
		if ($params['search'] != 'yes') {
			$limit = ($limit > $customlimit ) ? $customlimit  : $limit;
			$cat_id = ($_GET['cat_id']) ? $_GET['cat_id'] : $categs[0]['cat_name'];
			$cat_id = $this->get_cat($cat_id);
			$cat_id = $cat_id[0]->virtuemart_category_id;
			//echo $cat_id;
			$childcats = $catmodel->getChildCategoryList(1, $cat_id, null, null, true);
			$cats[] = $cat_id;

			$total = $this->total($cat_id);
			for ($k = 0; $k <= $total; $k += $limit) {
				$nav[$s] = $k;
				$s++;
			}

			$products[$cat_id] = $this->product_ids_by_cat_id($cat_id, $nav[$_REQUEST['navpage']], $limit, $params);
		} else if ($params['search'] == 'yes') {
			$limit = ($limit > $customlimit ) ? $customlimit  : $limit;
			$total = $this->total_by_search();
			for ($k = 0; $k <= $total; $k += $limit) {
				$nav[$s] = $k;
				$s++;
			}

			$products[] = $this->product_ids_by_search($nav[$_REQUEST['navpage']], $limit, $params);
			//echo "test";			exit;
		}


		if (($_REQUEST['searchword'] && $params['search'] == 'yes') || $params['search'] == 'no' || !$params['search']) {

			$html .= '<div class="search_results"> </div>';
			if (($_REQUEST['searchword'] && $params['search'] == 'yes' && count($products))) {
				$html .= '<div style="color:#fff;font-weight:bold; margin:10px; ">Search results found in these sections</div>';
			}

			if ($params['search'] != 'yes') {
				$html .= $this->get_cat_list_html($categs);
			}

			$html .= $this->get_nav_list_html($nav, $categs);
			$html .= '<div style="clear:both"></div>
        <!-- <div class="row loader_milti" id="loader_milti">Wait for page to load – Add multiple items then - Add Items to Order </div> !-->';
			if ($result = count($products) > 0) {
				$html .= '<div style="text-align:center;" > <div id="error"></div>';
				$html .= "<style type=\"text/css\">{$params['styling']}</style>";

				$html .= '<form class="' . $formclass . ' multijs-recalculate" name="addtocart" method="post" action="' . $uri->toString() . '" onsubmit="return validateForm' . $formclass . '()">';
				$html .= "<table class=\"productsnap\" width=\"{$params['width']}\" border=\"{$params['border']}\"  ";
				$html .= !empty($params['align']) ? "align=\"{$params['align']}\">" : ">";
				$html .= "\n";
				$currency = CurrencyDisplay::getInstance();
				$html .= "<tr style=''>\n";

				if ('y' == $params['showimage']) $html .= "<th style='text-align:center;'></th>\n";
				if ('y' == $params['showsku']) $html .= "<th style='text-align:center;'></th>\n";
				if ('y' == $params['showname']) $html .= "<th style='text-align:center;'>
                <input type='submit' title='Add Items to Order' 
                                 value='Add Items to Order' class='addtocart_button" . $formclass . "'></div>
                </th>\n";
				/* $html .= "<th style='width:200px;text-align:center;'>Product Attibutes</th>\n" ;*/
				if ('y' == $params['showdesc']) $html .= "<th></th>\n";
				if ('y' == $params['showpkg']) $html .= "<th></th>\n";
				if ('y' == $params['showprice']) $html .= "<th colspan='2'><div> </div> 
<div style='clear:both;' />\n";

				if ('y' == $params['showaddtocart']) {

					//$html .= '<input type="hidden" value="total" name="total" id="total">';
					//$html .= '<input type="hidden" value="totaltax' . $formclass . '" name="totaltax' . $formclass . '" id="totaltax' . $formclass . '">';
					//$html .= "<div> <div style='float:left;'>Total Price:  </div><div style='margin-left:5px;float:left;' id='prodtotal" . $formclass . "'>0</div><div style='float:left;margin-left:5px;'>  " . $currency->getSymbol() . "</div></div> <div style='clear:both;' /></th>\n";
				}
				$html .= "</tr>\n";
				$html .= "<tr style=''>\n";
				if ('y' == $params['showimage']) $html .= "<th style='text-align:center;'>Product Image</th>\n";
				if ('y' == $params['showsku']) $html .= "<th style='width:30px;text-align:center;'>Product SKU</th>\n";
				if ('y' == $params['showname']) $html .= "<th style='width:80px;text-align:center;'>Product Name</th>\n";
				/* $html .= "<th style='width:200px;text-align:center;'>Product Attibutes</th>\n" ;*/
				if ('y' == $params['showdesc']) $html .= "<th>Description</th>\n";
				if ('y' == $params['showpkg']) $html .= "<th style='width:80px;'>Pkg</th>\n";
				if ('y' == $params['showprice']) $html .= "<th style='width:200px;'>Price</th>\n";
				if ('y' == $params['showaddtocart']) $html .= "<th>Qty</th>\n";
				$html .= "</tr>\n";
				// set up how the rows and columns are displayed
				if ('v' == $params['displayeach']) {
					$row_sep_top = "<tr>\n";
					$row_sep_btm = "</tr>\n";
				} else {
					$row_sep_top = "";
					$row_sep_btm = "";
				}

				if ('h' == $params['displaylist']) {
					$start = "<tr>\n";
					$end = "</tr>\n";
				} else {
					$start = "";
					$end = "";
				}

				if ('h' == $params['displaylist'] && 'v' == $params['displayeach']) {
					$prod_top = "<td valign=\"top\"><table>\n";
					$prod_btm = "</table></td>\n";
				} else if ($params['displaylist'] == $params['displayeach']) {
					$prod_top = "";
					$prod_btm = "";
				} else {
					$prod_top = "<tr>\n";
					$prod_btm = "</tr>\n";
				}

				$i = 0;
				$html .= $start;


				foreach ($products as $key => $prod) {
					if ($params['search'] != 'yes') {
						$category_title = $catmodel->getCategory($key);
						$category_title = null;
						/* Printing category title*/
						if ($category_title) {
							$html .= $row_sep_top;
							$html .= "<td class=\"product_name\" align=\"center\">" . $category_title->category_name . "</td>\n";
							$html .= $row_sep_btm;
						}
					}
					foreach ($prod as $product) {
						$html .= $prod_top;
//
						$product = $productModel->getProduct($product->virtuemart_product_id);

						$url = $product->link;

						if ('y' == $params['showimage']) {
							$html .= $row_sep_top;

							$productModel->addImages($product);

							$product_thumb_image = $product->images[0]->displayMediaThumb('class="snapshotImage" title="' . $product->product_name . '" ', false, 'class="modal"');

							$html .= "<td class=\"image\" align=\"center\">";

							// $html .= "<a href=\"" . $url . "\">" . $product_thumb_image;
							// $html .= "</a></td>\n";
							$html .= $product_thumb_image;
							$html .= "</td>\n";
							$html .= $row_sep_btm;
						}

						if ('y' == $params['showsku']) {
							$html .= $row_sep_top;
							$html .= "<td class=\"product_name\" align=\"center\">{$product->product_sku}</td>\n";
							$html .= $row_sep_btm;
						}
						if ('y' == $params['showname']) {
							$html .= $row_sep_top;
							//$html .= "<td class=\"product_name\" align=\"center\"><a href=\"" . $url . "\">" . $product->product_name . "</a></td>\n";
							$html .= "<td class=\"product_name\" align=\"center\">" . $product->product_name . "</td>\n";
							$html .= $row_sep_btm;
						}

						$params['attribute'] = 'y';

						if ('y' == $params['showdesc']) {
							$html .= $row_sep_top;
							$html .= "<td class=\"desc\">" . $product->product_s_desc . "</td>\n";
							$html .= $row_sep_btm;
						}
						if ('y' == $params['showpkg']) {
							$html .= $row_sep_top;

							$html .= "<td class=\"price\">" . $product->product_packaging . " " . $product->product_unit . "</td>\n";
							$html .= $row_sep_btm;
						}
						if ('y' == $params['showprice']) {
							$currency = CurrencyDisplay::getInstance();


							if (!empty($product->prices['salesPrice'])) {
								$price = $currency->createPriceDiv('salesPrice', '', $product->prices, true);
							}

							if (!empty($product->prices['salesPriceWithDiscount'])) $price .= '<br/>Discount: ' . $currency->createPriceDiv('salesPriceWithDiscount', '', $product->prices, true);
							$html .= $row_sep_top;
							//$html .= "<td class=\"price\">".$PHPSHOP_LANG->_PHPSHOP_CART_PRICE .": ". number_format($price["product_price"],2) . " " . $price["product_currency"]."</td>\n";
							$html .= "<td class=\"price\">" . $price . "</td>\n";
							$html .= '<input type="hidden" value="' . $product->prices['salesPrice'] . '" name="pricequat" id="pricequa' . $product->virtuemart_product_id . '">';
							$html .= '<input type="hidden" value="' . $product->prices['taxAmount'] . '" name="pricetax" id="pricetax' . $product->virtuemart_product_id . '">';

							$html .= $row_sep_btm;
						}
						if ('y' == $params['showaddtocart']) {
							$params['quantity'][0] = 0;
							if (@$params['quantity'] > -1) {
								$qty = $params['quantity'][0];
							} else {
								$qty = $params['quantity'][0];
							}

							$html .= $row_sep_top;
							$html .= "<td class=\"addtocart\" style='width:70px;'>";
							/*$url = "index.php?page=shop.cart&func=cartAdd&quantity=$qty&product_id=" . $db->f( "product_id" ) ;
							$html .= "<a href=\"" . $sess->url( URL . $url ) . "\"> " . $VM_LANG->_('PHPSHOP_CART_ADD_TO') ;
							if( @$params['quantity'][$i] > 1 ) {
								$html .= " x$qty" ;
							}

							$html .= "</a><br />\n</td>" ;*/
							$html .= '
                                <span class="quantity-box">
                                <input min="0" type="number" value="' . $qty . '" name="quantity[]" id="quantity' . $product->virtuemart_product_id . '" size="4" class="quantity-input js-recalculate">
                                 <input type="hidden" value="' . $product->virtuemart_product_id . '" name="product_ids[]" id="product_ids' . $product->virtuemart_product_id . '" >                                 
                              </span>  
                               <span class="quantity-controls js-recalculate">
<input class="quantity-controls quantity-plus" id="quantity-plus' . $product->virtuemart_product_id . '" type="hidden">
<input class="quantity-controls quantity-minus" id="quantity-minus' . $product->virtuemart_product_id . '" type="hidden">
</span>    </span>
                          <div class="clear"></div>      
                                
                                </td>';
							$idi[$i] = $product->virtuemart_product_id;

							//    $product_currency = $currency->ensureUsingCurrencyCode($price["product_currency"]);
							$html .= $row_sep_btm;
						}
						$html .= $prod_btm;
						$i++;
					}
				}
				$html .= $end;

				$html .= ' </table>      
    <input type="hidden" value="multiCartAdd' . $formclass . '" name="func">
    <input type="hidden" value="' . $formclass . '" name="formid">
    <input type="hidden" value="31" name="Itemid">
    <input id="cat_id_input" type="hidden" value="' . $_GET['cat_id'] . '" name="cat_id_input">
	</form>';

				$html .= $this->get_nav_list_html($nav, $categs);
				$html .= '</nav>wwwtet';


				echo $html;


			} else {
				$html .= 'Product not found';
				echo $html;
			}
		} else {
			$html .= '<div class="search_results"> Only search key words without using numbers e.g Spiderman, Hulk, X-Men etc </div>';
			echo $html;
		}
	}


	function get_prodsnap_params($vm_productsnap_match, $param_defaults)
	{
		$params = explode(",", $vm_productsnap_match);
		foreach ($params as $param) {
			$param = explode("=", $param);
			if (isset($param_defaults[$param[0]])) {
				$param_defaults[$param[0]] = $param[1];
			}
		}
		$param_defaults['id'] = "'" . str_replace("|", "','", $param_defaults['id']) . "'";
		$param_defaults['quantity'] = explode("|", $param_defaults['quantity']);
		return $param_defaults;
	}

	function get_cat($cat_name)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('virtuemart_category_id')));
		$query->from($db->quoteName('#__virtuemart_categories_en_gb'));
		$query->where($db->quoteName('category_name') . ' LIKE ' . $db->quote($cat_name));


		$db->setQuery($query);
		return $results = $db->loadObjectList();
	}

	function categories_by_searchword($searchword, $categories)
	{
		$db = JFactory::getDBO();
		$result = $categories_ids = [];
		if ($searchword):
			$searchword = "  l.`product_name` LIKE '%{$searchword}%' AND  ";
			//foreach ($categories as $category) {
			$cat_id = ($category['cat_id']) ? $category['cat_id'] : [];
			$cat_q = ($cat_id) ? "  `pc`.`virtuemart_category_id` IN ({$cat_id}) AND  " : '';
			$cat_q = '';
			//echo "<br/> categories_by_searchword: ";
			/*
					$sql = "SELECT p.`virtuemart_product_id`, p.`product_sku`, `pc`.`virtuemart_category_id` as category
	 FROM `#__virtuemart_products` as p
	LEFT JOIN `#__virtuemart_products_en_gb` as l ON l.`virtuemart_product_id` = p.`virtuemart_product_id`
	 LEFT JOIN `#__virtuemart_product_shoppergroups` as ps ON p.`virtuemart_product_id` = `ps`.`virtuemart_product_id`
	 LEFT JOIN `#__virtuemart_product_categories` as pc ON p.`virtuemart_product_id` = `pc`.`virtuemart_product_id`
	  WHERE ( {$cat_q}   {$searchword}  p.`published`='1'  )
	   ORDER BY  p.`product_sku` asc ";

			*/

			$sql = "SELECT p.`virtuemart_product_id`, p.`product_sku`
 FROM `#__virtuemart_products` as p 
LEFT JOIN `#__virtuemart_products_en_gb` as l ON l.`virtuemart_product_id` = p.`virtuemart_product_id`
  WHERE (   {$searchword}  p.`published`='1'  )  
   ORDER BY  p.`product_sku` asc ";
			//print_r($sql);	exit;
			$db->setQuery($sql, 0, 1);
			$result = $db->loadAssocList();
			if ($result) {
				$categories_ids[] = $category;
				//	}
			}
		endif;
		return $categories_ids;
	}

	function product_ids_by_search($limitstart, $limit, $params)
	{
		//echo $limitstart;   echo "<br/>";	    echo $limit;

		$db = JFactory::getDBO();

		$jinput = JFactory::getApplication()->input;
		$searchword = $jinput->get('searchword', '', 'string');

		$cat_q = '';
		if ($params['search'] == 'yes') {
			$searchword = "  l.`product_name` LIKE '%{$searchword}%' AND  ";
		}

		$sql = "SELECT p.`virtuemart_product_id`, p.`product_sku`
 FROM `#__virtuemart_products` as p 
LEFT JOIN `#__virtuemart_products_en_gb` as l ON l.`virtuemart_product_id` = p.`virtuemart_product_id`
  WHERE (  {$searchword}  p.`published`='1'  )  
   ORDER BY  p.`product_sku` asc ";

		$db->setQuery($sql, $limitstart, $limit);
		$result = $db->loadObjectList();
		//echo "<pre>";print_r ($result); echo "<br/> product_ids_by_cat_id: ";		print_r($sql);

		return $result;
	}

	function product_ids_by_cat_id($cat_id, $limitstart, $limit, $params)
	{

		$db = JFactory::getDBO();

		$jinput = JFactory::getApplication()->input;
		$cat_name = $jinput->get('cat_id', '3', 'string');
		$searchword = "  l.`product_name` LIKE '{$cat_name}%' AND  ";


		$sql = "SELECT p.`virtuemart_product_id`, p.`product_sku`
 FROM `#__virtuemart_products` as p 
LEFT JOIN `#__virtuemart_products_en_gb` as l ON l.`virtuemart_product_id` = p.`virtuemart_product_id` 

  WHERE (   {$searchword}  p.`published`='1'  )  
   ORDER BY  p.`product_sku` asc ";

		$db->setQuery($sql, $limitstart, $limit);
		$result = $db->loadObjectList();
		//echo "<br/> product_ids_by_cat_id: ";		print_r($sql);

		return $result;
	}

	function total($cat_id)
	{


		$jinput = JFactory::getApplication()->input;
		$cat_name = $jinput->get('cat_id', '', 'string');
		$searchword = "  l.`product_name` LIKE '{$cat_name}%' ";
		$db = JFactory::getDBO();
		$total = 0;
		if ($cat_name):


			$sql = "SELECT COUNT(p.`virtuemart_product_id`) FROM `#__virtuemart_products` as p 
LEFT JOIN `#__virtuemart_products_en_gb` as l ON l.`virtuemart_product_id` = p.`virtuemart_product_id`
  WHERE ( {$searchword}   AND p.`published`='1' )  ";

			//


			$db->setQuery($sql);

			$total = $db->loadResult();;

		endif;
		//echo "<br/> total: ";			echo "{$total} : <br />".	$sql;
		return $total;
	}

	function total_by_search()
	{
		$searchword = ($this->searchword && $this->params['search'] == 'yes') ? $searchword = "  l.`product_name` LIKE '%{$this->searchword }%' AND  " : " ";
		$db = JFactory::getDBO();
		$total = null;

		if ($searchword):
			$sql = "SELECT COUNT(p.`virtuemart_product_id`) FROM `#__virtuemart_products` as p 
LEFT JOIN `#__virtuemart_products_en_gb` as l ON l.`virtuemart_product_id` = p.`virtuemart_product_id`
  WHERE  {$searchword}  p.`published`='1' ";
			//

			$db->setQuery($sql);
			$total = $db->loadResult();;
		endif;
		//echo "<br/> totalj: ";echo "$total: <br />" . $sql;
		return $total;
	}
}
