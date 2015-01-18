<?php
require_once '../include.php'; 
?>
<!doctype html>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html' charset='utf-8'>
<title>登陆</title>
<link type="text/css" rel="stylesheet" href="styles/reset.css">
<link type="text/css" rel="stylesheet" href="styles/main.css">
</head>

<body>
   <div class="logoBar login_logo">
       <div class="welcome">
          <h3 class="welcome_title">欢迎登陆</h3>
       </div>
   </div>


<div class="loginBox">
	<div class="login_cont">
	<form action="doLogin.php" method="post" >
			<ul class="login">
				<li class="l_tit">管理员帐号</li>
				<li class="mb_10"><input type="text"  name="username" placeholder="请输入管理员帐号"class="login_input user_icon"></li>
				<li class="l_tit">密码</li>
				<li class="mb_10"><input type="password"  name="password" placeholder="请输入密码" class="login_input password_icon"></li>
				<li class="l_tit">验证码</li>
				<li class="mb_10"><input type="text"  name="verify" class="login_input password_icon"></li>
				<img src="getVerify.php" alt="" />
				<li class="autoLogin"><input type="checkbox" id="a1" class="checked" name="autoFlag" value="1"><label for="a1">自动登陆(一周内自动登陆)</label></li>
				<li><input type="submit" value="" class="login_btn"></li>
			</ul>
		</form>
	</div>
</div>

<div class="hr_25"></div>
<div class="footer">
	<p>Copyright &copy; 2007 - 2014 南京师范大学版权所有&nbsp;&nbsp;&nbsp;</p>
</div>

</body>
</html>