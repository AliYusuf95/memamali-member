<?php
session_start();
require_once ('config.php');
require_once ('include.php');

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

checkSession(5);

if(isset($_POST['login']))
{
	try{
	$getuser=$db->prepare("SELECT * FROM admin WHERE username=? AND password=md5(?)");
	$getuser->execute(array($_POST['username'],$_POST['password']));
	$user = $getuser->fetch();
	if (!$getuser->rowCount())
	{
		header('Location:control.php?error');
		die();
	}
	else
		$_SESSION['login']=$user['username'];
		$_SESSION['open']=$user['open'];
        header('Location:control.php');
	}catch (PDOException $ex) {
		header('Location:control.php?error');
		die();
	}
	$_SESSION['LAST_ACTIVITY'] = time();
}

if (isset($_POST['logout']))
{
	session_destroy();
	header("location:control.php");
}

if (isset($_POST['download']))
	ExportUserToExcel("WHERE active=1");

if (isset($_POST['downloadAll']))
	ExportUserToExcel("");


if (isset($_POST['upload']))	
	InportExcel("user");

if (isset($_POST['child']) && isset($_POST['fatherCPR']))
{
	try {
		$db->beginTransaction();
		$deleteChild = $db->prepare("DELETE FROM `father` WHERE parent=?");
		$deleteChild-> execute(array($_POST['fatherCPR']));
		$addChild = $db->prepare("INSERT INTO `father` VALUES (?,?)");
		if (isset($_POST['childCPR']))
			for ($i=0;$i<count($_POST['childCPR']);$i++)
				$addChild -> execute(array($_POST['fatherCPR'],$_POST['childCPR'][$i]));
		$db->commit();
		$success = [1,'تم حفظ بيانات الأبناء بنجاح ..'];
	}catch (PDOException $ex) {
		$success = [0,'فشل في إضافة البيانات .. يرجى المحاولة مرة أخرى'];
	}
}

if (isset($_POST['valid']) && isset($_POST['CPR']))
{
	try {
		$db->beginTransaction();
		$user = $db->prepare("SELECT `name`, `CPR`, `dob`, `phone`, `edLevel`, `major`, `TC`, `emState`, `employer`, `jobName`, `maState`, `kidNum`, `involved`, `involvedName`, `hobby`, `otherHobby`, `boys` FROM queue WHERE CPR=".$_POST['CPR']);
		$user -> execute();
		$user = $user->fetch(PDO::FETCH_ASSOC);
		if (findUser($_POST['CPR'],'user'))
			$success = updateuser($user);
		else
			$success = add2user($user);
		if (!$success[0])
			$db-> rollBack();
		else
		{
			$deletequeue = $db->prepare("delete FROM queue WHERE CPR=".$_POST['CPR']);
			$deletequeue -> execute();
			$db->commit();
		}
	}catch (PDOException $ex) {
		$success = [0,'فشل في إضافة البيانات .. يرجى المحاولة مرة أخرى'];
	}
}

if (isset($_POST['update']))
{
	try {
		$user = $_POST;
		unset($user['update']);
		$user['hobby'] = implode(", ", $user['hobby']);
		if (isset($user['oldCPR']))
		{
			$deleteOld = $db->prepare("delete FROM user WHERE CPR=".$_POST['oldCPR']);
			$deleteOld -> execute();
			unset($user['oldCPR']);
			$success = add2user($user);
		}
		else if (findUser($_POST['CPR'],'user'))
			$success = updateuser($user);
		else
			$success = add2user($user);
		if (findUser($_POST['CPR'],'queue'))
		{
			$deletequeue = $db->prepare("delete FROM queue WHERE CPR=".$_POST['CPR']);
			$deletequeue -> execute();
		}
	}catch (PDOException $ex) {
		$success = [0,"فشل في إضافة البيانات .. يرجى المحاولة مرة أخرى"];
	}
}

if (isset($_POST['add']))
{
	$user = $_POST;
	unset($user['add']);
	if(isset($user['hobby']))
	$user['hobby'] = implode(", ", $user['hobby']);
	$success = add2user($user);
}

if (isset($_POST['delete']))
{
	try {
		$deletequeue = $db->prepare("delete FROM queue WHERE CPR=".$_POST['CPR']);
		$deletequeue -> execute();
		$success = [1,'تم حذف الطلب بنجاح'];
	}catch (PDOException $ex) {
		$success = [0,'فشل في حذف الطلب .. يرجى المحاولة مرة أخرى'];
	}
}

if (isset($_POST['delete_user']))
{
	try {
		$deleteuser = $db->prepare("delete FROM user WHERE CPR=".$_POST['CPR']);
		$deleteuser -> execute();
		if (findUser($_POST['CPR'],'queue'))
		{
			$user = $db->prepare("delete FROM queue WHERE CPR=".$_POST['CPR']);
			$user ->execute();
		}
		$deleteparent = $db->prepare("DELETE FROM father WHERE parent=".$_POST['CPR']." OR child=".$_POST['CPR']);
		$deleteparent ->execute();
		$success = [1,'تم حذف الحساب بنجاح'];
	}catch (PDOException $ex) {
		$success = [0,'فشل في حذف الحساب .. يرجى المحاولة مرة أخرى'];
	}
	unset($_POST['child']);
}

if (isset($_POST['open']) || isset($_POST['close']))
{
	try {
		$update = $db->prepare("UPDATE admin SET open = ?");
		if (isset($_POST['open']))
		{
			$update -> execute(array(1));
			$success = [1,'تم فتح فترة التعديل'];
			$_SESSION['open']=1;
		}
		else
		{
			$update -> execute(array(0));
			$success = [1,'تم غلق فترة التعديل'];
			$_SESSION['open']=0;
		}
	}catch (PDOException $ex) {
		$success = [0,'فشل في تغيير حالة التعديل .. يرجى المحاولة مرة أخرى'];
	}
}
$users = null;
$user = null;
?>
<!DOCTYPE html>
<html lang="ar">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
	<link rel="icon" href="http://www.memamali.com/favicon.png" title="Favicon" />
	<link rel="apple-touch-icon" href="M-Logo.png" />
	<title>مأتم الإمام علي (ع) - تحديث البيانات</title>
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
	<!-- Load Bootstrap RTL theme -->
	<link rel="stylesheet" href="css/bootstrap-rtl.css">
    <!-- Load DataTables CSS -->
    <link rel="stylesheet" href="js/datatables/jquery.dataTables.min.css">
    <link rel="stylesheet" href="js/datatables/dataTables.bootstrap.css">
      <!-- Jquery-ui style -->
      <link rel="stylesheet" href="css/custom-theme/jquery-ui-1.10.0.custom.css">
      <link rel="stylesheet" href="css/custom-theme/jquery.ui.1.10.0.ie.css">
	<!-- Custom style -->
	<link rel="stylesheet" href="css/default.css">
	<link rel="stylesheet" href="css/control.css">
	<script src="js/jquery-2.2.3.min.js"></script>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	</head>

	<body>
	<div class="container">
		<div class="header clearfix" style="padding-bottom: 5px;">
			<nav>
			  <ul class="nav nav-pills pull-left">
				<li role="presentation"><a href="./">إستمارة التحديث</a></li>
			  </ul>
			</nav>
			<a class="navbar-brand navbar-right" style="padding: 0 0 0 25px;">
				<img alt="Brand" src="M-Logo.png" style="width: 45px; margin-right: 15px;">
			</a>
			<h3 class="navbar-text navbar-right hidden-xs" style="margin-top: 3px;">مأتم الإمام علي (ع)</h3>
		</div>
		<div class="jumbotron">
			<h1>لوحة تحكم بيانات العضوية<br><small>مأتم الإمام علي(ع) -قرية بوري- <?php echo date("Y"); ?>م</small></h1>
			<p class="lead"></p>
		</div>
	<?php
	if (!isset($_SESSION['login']))
	{
	?>
		<div class="row">
			<div class="col-xs-10 col-xs-offset-1 col-md-8 col-md-offset-2">
				<div class="login-panel panel panel-default" style="margin-top: 10%" >
					<h3 class="form-signin-heading" style="text-align:center">تسجيل دخول لوحة التحكم</h3>
					<div class="panel-body">
						<legend style="margin-top: -10px"></legend>							
						<form method="POST">
							<fieldset>
								<legend>
								<?php 
									if (isset($_GET['error']))
									echo "<h5 style='color:red;'>إسم المستخدم أو كلمة السر غير صحيحة</h5>"; 
								?>
									<div class="form-group <?php if (isset($_GET['error'])) echo "has-error";?>">
										<div class="input-group">
											<span style="border-right-color: rgb(204, 204, 204);" class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
											<input class="form-control" placeholder="إسم المستخدم" name="username" type="text" required="required"/>
										</div>
									</div>
									<div class="form-group <?php if (isset($_GET['error'])) echo "has-error";?>">
										<div class="input-group">
											<span style="border-right-color: rgb(204, 204, 204);" class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
											<input class="form-control" placeholder="الرقم السري" name="password" type="password" required="required" />
										</div>
									</div>
								</legend>
								<button type="submit" name="login" class="btn btn-lg btn-primary btn-block">تسجيل دخول</button>
								<br/>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
		</div>
	<?php
	}
	else if (isset($_POST['valid']) || isset($_POST['delete']))
	{
	?>
		<div class="row marketing" style="margin-top: 0;">
			<div class="col-md-12">
			<?php
			if (isset($success))
			{
			?>
			<div class="alert alert-<?php if ($success[0]) echo 'success'; else echo 'danger'; ?> alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<?php echo $success[1]; ?>
			</div>
			<?php
			}
			?>
			<div class="table-responsive" id="table">
				<table class="table table-hover">
					<thead>
						<tr>
							<th class="col-xs-1">#</th>
							<th class="col-xs-3">الإسم</th>
							<th class="col-xs-2">الرقم الشخصي</th>
							<th class="col-xs-6">خيارات</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$print = true;
					try
					{
						$users = $db->prepare("SELECT * FROM queue");
						$users ->execute();
						if ($users ->rowCount()==0)
							$print=false;
					} catch (PDOException $ex) {
						$print = false;
					}
					if ($print)
					for ($i=1;$user = $users->fetch();$i++)
					{
					?>
						<tr <?php if (!findUser($user['CPR'],'user'))echo "class='danger'";?>>
							<td onclick="showQueueInfo('<?php echo $user['CPR'] ?>'<?php if (!findUser($user['CPR'],'user'))echo ",'إستمارة طلب تسجيل عضوية جديدة'";?>)" ><?php echo $i ?></td>
							<td onclick="showQueueInfo('<?php echo $user['CPR'] ?>'<?php if (!findUser($user['CPR'],'user'))echo ",'إستمارة طلب تسجيل عضوية جديدة'";?>)" ><?php echo $user['name'] ?></td>
							<td onclick="showQueueInfo('<?php echo $user['CPR'] ?>'<?php if (!findUser($user['CPR'],'user'))echo ",'إستمارة طلب تسجيل عضوية جديدة'";?>)" ><?php echo $user['CPR'] ?></td>
							<td>
							<form method="POST" id="queueForm" action="control.php" onsubmit="return confDelete(kind);">
								<input type="hidden" name="CPR" value="<?php echo $user['CPR'] ?>"/>
								<div class="btn-group"  style="width:350px;">
									<button type="submit" name="edit" class="btn btn-default" onclick="kind='تحرير'"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> تحرير</button>
									<button type="submit" name="delete" class="btn btn-danger" onclick="kind='حذف'"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> حذف</button>
									<button type="submit" name="valid" class="btn btn-success" onclick="kind='حفظ'"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> حفظ</button>
									<button type="button" class="btn btn-primary" onclick="printModal('<?php echo $user['CPR'] ?>','previewModal','q',<?php if (findUser($user['CPR'],'user'))echo "'مأتم الإمام علي (ع) - إستمارة  تعديل بيانات العضوية'"; else echo "'مأتم الإمام علي (ع) - إستمارة  تسجيل عضوية جديدة'";?>,'حررفي: <?php echo date('Y-m-d'); ?>');"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> طباعة</button>
								</div>
							</form>
							</td>
						</tr>
					<?php
					}
					?>
					</tbody>
				</table>
			</div>
			<p class="help-block"><span class="text-danger"><strong><span class="glyphicon glyphicon-alert" aria-hidden="true"></span> تنبيه:</strong></span> لون الطلب الأحمر يدل على طلب تسجيل عضوية جديدة</p>
			</div>
		</div>
		<script>
		document.getElementById('table').scrollLeft += 9999;
		</script>
		<div class="row marketing" style="margin-top: 0;">
			<div class="col-md-12">
				<div class="well center-block" style="max-width: 400px;">
					<a role="button" type="button" class="btn btn-default btn-lg btn-block" href="control.php">رجوع إلى لوحة التحكم</a>
				</div>
			</div>
		</div>
	<?php
	}
	else if (isset($_POST['new']))
		print_new_form();
	else if (isset($_POST['edit']))
	{
		$print = true;
		try
		{
			$user = $db->prepare("SELECT * FROM queue WHERE CPR=".$_POST['CPR']);
			$user ->execute();
			$user = $user->fetch(PDO::FETCH_ASSOC);
		} catch (PDOException $ex) {
			$print = false;
		?>
		<div class="alert alert-danger alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			حدث خطأ في جلب البيانات يرجى المحاولة مرة أخرى
		</div>
		<?php
		}// catch end

		if ($print)
			print_user_form($user);
	}
	else if (isset($_POST['edit_user']))
	{
		$print = true;
		try
		{
			$user = $db->prepare("SELECT * FROM user WHERE CPR=".$_POST['CPR']);
			$user ->execute();
			if ($user->rowCount()==0)
				$print = false;
			$user = $user->fetch(PDO::FETCH_ASSOC);
		} catch (PDOException $ex) {
			$print = false;
		?>
		<div class="alert alert-danger alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			حدث خطأ في جلب البيانات يرجى المحاولة مرة أخرى
		</div>
		<?php
		}// catch end
		
		if ($print)
			print_user_form($user);
		else{
			?>
		<div class="alert alert-danger alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			لاتوجد بيانات للعضو
		</div>
		<?php
		}
		?>
		<div class="row marketing" style="margin-top: 0;">
			<div class="col-md-12">
				<div class="well center-block" style="max-width: 400px;">
					<a role="button" type="button" class="btn btn-default btn-lg btn-block" href="control.php">رجوع إلى لوحة التحكم</a>
				</div>
			</div>
		</div>
		<?php
	}
	else if (isset($_GET['CPR']) && strlen($_GET['CPR']) == 9)
	{
		$parents = null;
		$brothers = null;
		$childs = null;
		getFamily($_GET['CPR']);
		?>
		<div class="row marketing" style="margin-top: 0;">
			<div class="col-md-12">
				<h3 class="control-label" style="margin-top: 15px;">بيانات العضو - <span class="small"><?php echo getName($_GET['CPR']); ?></span></h3>
				<?php if (getActive($_GET['CPR'])==0) echo'<p class="help-block"><span class="text-danger"><strong><span class="glyphicon glyphicon-alert" aria-hidden="true"></span> تنبيه:</strong> هذا العضو غير فعّال</span></p>';?>
				<form method="POST" action="control.php" onsubmit="return confDelete(kind);">
				<div class="btn-group btn-block">
					<button name="search" class="col-xs-12 col-sm-4 btn btn-default" type="submit" onclick="kind='true'"><span class="glyphicon glyphicon-wrench" aria-hidden="true"></span> تعديل قائمة الأبناء</button>
					<button class="col-xs-12 col-sm-4 btn btn-default" type="button" onclick="showUserInfo('<?php echo $_GET['CPR'] ?>')"><span class="glyphicon glyphicon-file" aria-hidden="true"></span> استعراض بيانات العضو</button>
					<button class="col-xs-12 col-sm-4 btn btn-primary" type="button" onclick="printModal('<?php echo $_GET['CPR'] ?>','previewModal','u','مأتم الإمام علي (ع) - إستمارة بيانات العضوية','إعتمد بواسطة: <?php echo getApprovedBy($_GET['CPR']); ?> - في تاريخ: <?php echo getLastEdit($_GET['CPR']); ?>');" ><span class="glyphicon glyphicon-print" aria-hidden="true"></span> طباعة بيانات العضو</button>
				</div>
				<div class="btn-group btn-block">
					<button name="edit_user" class="col-xs-12 col-sm-6 btn btn-warning" type="submit" onclick="kind='تحرير البيانات'"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> تحرير البيانات</button>
					<button name="delete_user" class="col-xs-12 col-sm-6 btn btn-danger" type="submit" onclick="kind='حذف البيانات'"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> حذف بيانات العضو</button>
				</div>
				<input type="hidden" name="CPR" value="<?php echo $_GET['CPR'] ?>">
				<input type="hidden" name="child">
				</form>
			</div>
		</div>
		<div class="row marketing" style="margin-top: 0;">
			<div class="col-md-12">
				<ul id="familyTab" class="nav nav-tabs">
				  <li class="active"><a href="#dad" data-toggle="tab"><strong class="lead">الآباء</strong></a></li>
				  <li><a href="#bro" data-toggle="tab"><strong class="lead">الأخوة</strong></a></li>
				  <li><a href="#child" data-toggle="tab"><strong class="lead">الأبناء</strong></a></li>
				</ul>
			</div>
			<div id="familyTabContent" class="tab-content">
				<div class="tab-pane fade in active" id="dad">
					<table style="width:96%;margin-right: 2%;margin-left: 1%;" class="table table-hover">
						<thead>
							<tr>
								<th class="col-xs-1">#</th>
								<th class="col-xs-6">الإسم</th>
								<th class="col-xs-5">الرقم الشخصي</th>
							</tr>
						</thead>
						<tbody>
						<?php
						for ($i=0;$i<count($parents["CPR"]);$i++)
						{
						?>
							<tr>
								<td><?php echo $i+1 ?></td>
								<td><?php echo $parents["name"][$i] ?></td>
								<td><a class="btn btn-default" href="control.php?CPR=<?php echo $parents["CPR"][$i] ?>"><?php echo $parents["CPR"][$i] ?></td>
							</tr>
						<?php
						}
						?>
						</tbody>
					</table>
				</div>
				<div class="tab-pane fade" id="bro">
					<table style="width:96%;margin-right: 2%;margin-left: 1%;" class="table table-hover">
						<thead>
							<tr>
								<th class="col-xs-1">#</th>
								<th class="col-xs-6">الإسم</th>
								<th class="col-xs-5">الرقم الشخصي</th>
							</tr>
						</thead>
						<tbody>
						<?php
						for ($i=0;$i<count($brothers["CPR"]);$i++)
						{
						?>
							<tr>
								<td><?php echo $i+1 ?></td>
								<td><?php echo $brothers["name"][$i] ?></td>
								<td><a class="btn btn-default" href="control.php?CPR=<?php echo $brothers["CPR"][$i] ?>"><?php echo $brothers["CPR"][$i] ?></td>
							</tr>
						<?php
						}
						?>
						</tbody>
					</table>
				</div>
				<div class="tab-pane fade" id="child">
					<table style="width:96%;margin-right: 2%;margin-left: 1%;" class="table table-hover">
						<thead>
							<tr>
								<th class="col-xs-1">#</th>
								<th class="col-xs-6">الإسم</th>
								<th class="col-xs-5">الرقم الشخصي</th>
							</tr>
						</thead>
						<tbody>
						<?php
						for ($i=0;$i<count($childs["CPR"]);$i++)
						{
						?>
							<tr>
								<td><?php echo $i+1 ?></td>
								<td><?php echo $childs["name"][$i] ?></td>
								<td><a class="btn btn-default" href="control.php?CPR=<?php echo $childs["CPR"][$i] ?>"><?php echo $childs["CPR"][$i] ?></td>
							</tr>
						<?php
						}
						?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="row marketing" style="margin-top: 0;">
			<div class="col-md-12">
				<div class="well center-block" style="max-width: 400px;">
					<a role="button" type="button" class="btn btn-default btn-lg btn-block" href="control.php">رجوع إلى لوحة التحكم</a>
				</div>
			</div>
		</div>
		<?php
	}
	else if (isset($_POST['child']))
	{
		if (isset($success))
		{
		?>
		<div class="row marketing" style="margin-top: 0;">
			<div class="col-md-12">
				<div class="alert alert-<?php if ($success[0]) echo 'success'; else echo 'danger'; ?> alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<?php echo $success[1]; ?>
				</div>
			</div>
		</div>
		<?php
		}
		?>
		<div class="panel panel-default">
			<div class="panel-heading">
			<form method="POST" >
				<div class="form-group">
				<label class="col-sm-2" style="font-size:14pt;">قائمة الأبناء</label>
					<div class="input-group">
						<span class="input-group-btn">
							<button class="btn btn-default" id="search" name="search" type="submit" disabled="disabled"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> جلب المعلومات</button>
						</span>
						<input type="text" class="form-control" id="CPR" name="CPR" placeholder="رقم الأب الشخصي .." maxlength="9" onkeyup="isUser(this.value)" autocomplete="off">
						<input type="hidden" name="child">
					</div>
				</div>
			</form>
			</div>
			<form method="POST" onsubmit="return checkChildForm()" >
				<div class="panel-body">
					<div class="table-responsive" id="table">
						<table class="table table-hover">
							<tbody id="tableBody">
							<?php if (isset($_POST['search']) || isset($_POST['fatherCPR']))
							{
							?>
								<tr>
									<th colspan="2">معلومات الأب</th>
									<th>خيارات</th>
								</tr>
								<tr>
									<td class="col-xs-3">
										<input style="width:150px;" value="<?php echo $_POST['CPR']; ?>" class="form-control" id="fatherCPR" disabled="disabled">
										<input type="hidden" name="fatherCPR" value="<?php echo $_POST['CPR']; ?>" >
										<input type="hidden" name="CPR" value="<?php echo $_POST['CPR']; ?>" >
									</td>
									<td id="fatherName">
									<?php echo getName($_POST['CPR']); ?>
									</td>
									<td>
									<div class="btn-group"  style="width:270px;">
										<button type="submit" id="save" name="child" class="btn btn-success" ><span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span> حفظ</button>
										<button type="button" name="edit" class="btn btn-default" onclick="addchildRow()" ><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> إضافة إبن</button>
										<a role="button" type="button" class="btn btn-default" href="control.php?CPR=<?php echo $_POST['CPR']; ?>"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> بيانات العضو</a>
									</div>
									</td>
								</tr>
								<?php
									getFamily($_POST['CPR']);
								?>
								<tr>
									<th colspan="2">معلومات الأبناء</th>
									<th>خيارات</th>
									<input type="hidden" id="childNum" value="<?php echo count($childs["CPR"]) ?>" />
								</tr>
								<?php
								if (count($childs["CPR"])>0)
								for ($i=0;$i<count($childs["CPR"]);$i++)
								{
								?>
								<tr id="childRow<?php echo $i+1 ?>">
									<td>
									<div style="padding-right: 0; padding-left: 5px;">
										<div class="form-group has-success has-feedback">
										<input class="form-control" type="text" id="childCPR<?php echo $i+1 ?>" value="<?php echo $childs["CPR"][$i] ?>" disabled="disabled">
										<input type="hidden" name="childCPR[]" value="<?php echo $childs["CPR"][$i] ?>" >
										<span class="glyphicon glyphicon-ok form-control-feedback" aria-hidden="true"></span>
										</div>
									</div>
									</td>
									<td class="col-sm-4">
									<?php echo $childs["name"][$i] ?>
									</td>
									<td>
										<button type="button" class="btn btn-danger" onclick="byId('childRow<?php echo $i+1 ?>').remove();" ><span class="glyphicon glyphicon-minus" aria-hidden="true"></span> حذف الإبن</button>
									</td>
								</tr>
							<?php
								}
							}
							?>
							</tbody>
						
						</table>
					</div>
					<script>document.getElementById('table').scrollLeft += 9999;</script>
				</div>
			</form>
		</div>
		<div class="row marketing" style="margin-top: 0;">
			<div class="col-md-12">
				<div class="well center-block" style="max-width: 400px;">
					<a role="button" type="button" class="btn btn-default btn-lg btn-block" href="control.php">رجوع إلى لوحة التحكم</a>
				</div>
			</div>
		</div>
		<?php
	}
	else
	{
	try{
		$user= $db->prepare("select CPR from queue");
		$user->execute();
		$num = $user->rowCount();
	}catch(PDOException $ex) {
		$num = "-";
	}
	?>
		<div class="row marketing" style="margin-top: 0;">
			<div class="col-md-12">
			<?php
			if (isset($success))
			{
			?>
			<div class="alert alert-<?php if ($success[0]) echo 'success'; else echo 'danger'; ?> alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<?php echo $success[1]; ?>
			</div>
			<?php
			}
			?>
			<div class="well center-block" style="max-width: 400px;">
			    <h3 class="control-label hidden" style="margin-top: 15px;">عدد المتعهدين: (<?php echo usersInMeetingCount(); ?>)</h3>
				<form role="form" method="POST" action="control.php">
					<button type="submit" name="new" class="btn btn-primary btn-lg btn-block"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> تسجيل عضوية جديدة</button>
					<button name="valid" class="btn btn-default btn-lg btn-block"><span class="glyphicon glyphicon-inbox" aria-hidden="true"></span> طلبات التعديل <span class="label label-<?php if ($num>0) echo "danger"; else echo "default"; ?>"><?php echo $num; ?></span></button>
					<h3 class="control-label" style="margin-top: 15px;">فترة تعديل البيانات <small>لأعضاء الجمعية العمومية</small> :</h3>
					<div class="btn-group btn-block btn-group-justified" role="group">
						<div class="btn-group btn-group-lg" role="group">
							<button type="submit" name="open" class="btn btn-<?php if($_SESSION['open']==1) echo "success"; else echo "default"; ?>"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> مفتوحة</button>
						</div>
						<div class="btn-group btn-group-lg" role="group">
							<button type="submit" name="close" class="btn btn-<?php if($_SESSION['open']==0) echo "danger"; else echo "default"; ?>"><span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span> مغلقة</button>
						</div>
					</div>
				</form>
				<div class="form-group">
				<form role="form">
					<h3 class="control-label" style="margin-top: 15px;">عرض بيانات العضو: </h3>
					<div class="row">
					  <div class="col-lg-12">
						<div class="input-group input-group-lg">
						  <span class="input-group-btn">
							<button id="search" class="btn btn-default" type="submit" disabled="disabled"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> بحث</button>
						  </span>
						  <input type="text" name="CPR" id="CPR" class="form-control" placeholder="الرقم الشخصي .." maxlength="9" onkeyup="isUser(this.value)" autocomplete="off">
						</div>
					  </div>
					</div>
				</form>
				</div>
				<!--form role="form" method="POST">
					<button type="submit" name="child" class="btn btn-default btn-lg btn-block"><span class="glyphicon glyphicon-wrench" aria-hidden="true"></span> تعديل قائمة الأبناء</button>
				</form-->
			</div>
			<div class="well center-block" style="max-width: 400px;">
				<form method="POST" action="control.php" enctype="multipart/form-data">
					<div class="form-group">
						<h3 class="control-label" style="margin-top: 0;">رفع البيانات <small>فقط ملفات الأكسل</small> :</h3>
						<input class="form-control" type="file" name="file" id="file">
					</div>
					<button type="submit" class="btn btn-warning btn-lg btn-block" name="upload"><span class="glyphicon glyphicon-cloud-upload" aria-hidden="true"></span> رفع البيانات</button>
					<button type="submit" name="downloadAll" class="btn btn-primary btn-lg btn-block"><span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span> تنزيل جميع البيانات</button>
					<button type="submit" name="download" class="btn btn-success btn-lg btn-block"><span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span> تنزيل قائمة الأعضاء المفعّلين</button>
				</form>
			</div>
			<div class="well center-block" style="max-width: 400px;">
					<div class="form-group">
						<h3 class="control-label" style="margin-top: 0;">بيانات سجل الناخبين</h3>
					</div>
					<div class="alert alert-info" role="alert">
					    <strong>إجمالي عدد الناخبين:</strong>
					    <?php echo usersInVoting(); ?>
					</div>
					<div class="alert alert-success" role="alert">
					    <strong>عدد الناخبين المسجلين:</strong>
					    <?php echo usersRegisteredoVoting(); ?>
					</div>
					<div class="alert alert-warning" role="alert">
					    <strong>المتبقي:</strong>
					    <?php echo (usersInVoting() - usersRegisteredoVoting()); ?>
					</div>
			</div>
			<div class="well center-block" style="max-width: 400px;">
				<form method="POST">
					<button type="submit" class="btn btn-danger btn-lg btn-block" name="logout"><span class="glyphicon glyphicon-off" aria-hidden="true"></span> خروج</button>
				</form>
			</div>
			</div>
		</div>
	<?php
	}
	?>
		<footer class="footer">
			<p>مأتم الإمام علي (ع)</p>
		</footer>
	</div> <!-- /container -->
	<!-- Modal -->
		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title text-danger" id="myModalLabel">بعض المعلومات ناقصة أو غير صحيحة !!</h4>
					</div>
					<div class="modal-body">
						<div class="container-fluid">
							<div class="row">
								<div class="col-md-12" id="error">
									
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" data-dismiss="modal">تراجع</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header" id="previewModalHeader">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title text-danger" id="previewModalLabel"></h4>
					</div>
					<div class="modal-body">
						<div class="container-fluid">
							<div class="row">
								<div class="col-md-12" id="info">
									
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer" id="previewModalFooter">
						<button type="button" id="hideModal" class="btn btn-danger" data-dismiss="modal">تراجع</button>
					</div>
				</div>
			</div>
		</div>
    <script src="js/jquery-2.2.3.min.js"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="default.js?version=<?php echo time(); ?>"></script>
    <script src="control.js?version=<?php echo time(); ?>"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/datatables/jquery.dataTables.min.js"></script>
    <script src="js/datatables/dataTables.bootstrap.min.js"></script>
	</body>
</html>