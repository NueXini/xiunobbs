<?php

function admin_token_check() {
	global $longip, $time, $useragent, $conf;
	$useragent_md5 = md5($useragent);
	$key = md5($longip.$useragent_md5.$conf['auth_key']);
	
	$admin_token = param('bbs_admin_token');
	if(empty($admin_token)) {
		$_REQUEST[0] = 'index';
		$_REQUEST[1] = 'login';
	} else {
		$s = xn_decrypt($admin_token, $key);
		if(empty($s)) {
			setcookie('bbs_admin_token', '', 0, '', '', '', TRUE);
			message(-1, lang('admin_token_error'));
		}
		list($_ip, $_time) = explode("\t", $s);
		// 后台超过 3600 自动退出。
		if($_ip != $longip || $time - $_time > 3600) {
			setcookie('bbs_admin_token', '', 0, '', '', '', TRUE);
			message(-1, lang('admin_token_expiry'));
		}
		// 超过半小时，重新发新令牌，防止过期
		if($time - $_time > 1800) {
			admin_token_set();
		}
	}
}

function admin_token_set() {
	global $longip, $time, $useragent, $conf;
	$useragent_md5 = md5($useragent);
	$key = md5($longip.$useragent_md5.$conf['auth_key']);
	
	$admin_token = param('bbs_admin_token');
	$s = "$longip	$time";
	
	$admin_token = xn_encrypt($s, $key);
	setcookie('bbs_admin_token', $admin_token, $time + 3600, '',  '', 0, TRUE);
}

function admin_token_clean() {
	global $time;
	setcookie('bbs_admin_token', '', $time - 86400, '', '', 0, TRUE);
}

// bootstrap style
function admin_tab_active($arr, $active) {
	$s = '';
	foreach ($arr as $k=>$v) {
		$s .= '<a role="button" class="btn btn btn-secondary'.($active == $k ? ' active' : '').'" href="'.$v['url'].'">'.$v['text'].'</a>';
	}
	return $s;
}
?>