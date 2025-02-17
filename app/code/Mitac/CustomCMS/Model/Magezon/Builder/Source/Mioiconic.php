<?php


namespace Mitac\CustomCMS\Model\Magezon\Builder\Source;

class Mioiconic
{
	public function getConfig()
	{	
		$icons = [
			'mio-icon-up-open' 				=> 'up-open',
			'mio-icon-down-open'			=> 'down-open',
			'mio-icon-left-open' 			=> 'left-open',
			'mio-icon-right-open-big' 		=> 'right-open',
			'mio-icon-open-up' 				=> 'open-up',
			'mio-icon-open-down' 			=> 'open-down',
			'mio-icon-oepn-left' 			=> 'oepn-left',
			'mio-icon-open-right' 			=> 'open-right',
			'mio-icon-a-up' 				=> 'a-up',
			'mio-icon-a-down' 				=> 'a-down',
			'mio-icon-a-left' 				=> 'a-left',
			'mio-icon-a-right' 				=> 'a-right',
			'mio-icon-asc' 					=> 'asc',
			'mio-icon-desc' 				=> 'desc',
			'mio-icon-checkmark' 			=> 'checkmark',
			'mio-icon-cancel' 				=> 'cancel',
			'mio-icon-yes' 					=> 'yes',
			'mio-icon-no' 					=> 'no',
			'mio-icon-cart' 				=> 'cart',
			'mio-icon-compare' 				=> 'compare',
			'mio-icon-delete' 				=> 'delete',
			'mio-icon-error' 				=> 'error',
			'mio-icon-fax' 					=> 'fax',
			'mio-icon-filter' 				=> 'filter',
			'mio-icon-heart' 				=> 'heart',
			'mio-icon-location' 			=> 'location',
			'mio-icon-menu' 				=> 'menu',
			'mio-icon-phone' 				=> 'phone',
			'mio-icon-play' 				=> 'play',
			'mio-icon-read-more' 			=> 'read-more',
			'mio-icon-search' 				=> 'search',
			'mio-icon-shop' 				=> 'shop',
			'mio-icon-slick-next' 			=> 'slick-next',
			'mio-icon-slick-prev' 			=> 'slick-prev',
			'mio-icon-update' 				=> 'update',
			'mio-icon-fb' 					=> 'fb',
			'mio-icon-twitter' 				=> 'twitter',
			'mio-icon-user' 				=> 'user',
			'mio-icon-users' 				=> 'users',
			'mio-icon-vimeo' 				=> 'vimeo',
			'mio-icon-vk' 					=> 'vk',
			'mio-icon-laz' 					=> 'laz',
			'mio-icon-youtube' 				=> 'youtube',
			'mio-icon-yandex-zen' 			=> 'yandex-zen',
			'mio-icon-instagram' 			=> 'instagram',
			'mio-icon-pin' 					=> 'pin',
			'mio-icon-home' 				=> 'home',
			'mio-icon-mobile' 				=> 'mobile',
			'mio-icon-address' 				=> 'address',
			'mio-icon-bike' 				=> 'bike',
			'mio-icon-car' 					=> 'car',
			'mio-icon-clock' 				=> 'clock',
			'mio-icon-calendar' 			=> 'calendar',
			'mio-icon-file' 				=> 'file',
			'mio-icon-chat' 				=> 'chat',
			'mio-icon-download' 			=> 'download',
			'mio-icon-upload' 				=> 'upload',
			'mio-icon-credit-card' 			=> 'credit-card',
			'mio-icon-pc' 					=> 'pc',
			'mio-icon-laptop' 				=> 'laptop',
			'mio-icon-folder' 				=> 'folder',
			'mio-icon-edit' 				=> 'edit',
			'mio-icon-tip' 					=> 'tip',
			'mio-icon-undo' 				=> 'undo',
			'mio-icon-redo' 				=> 'redo'
		];

		$options = [];
		foreach ($icons as $value => $label) {
			$options[] = [
				'label' => $label,
				'value' => $value
			];
		}

		return $options;
	}
}