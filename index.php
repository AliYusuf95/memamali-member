<?php
session_start();
require_once ('config.php');
require_once ('include.php');
checkSession(5);

try {
	$getstate=$db->prepare("SELECT open FROM admin");
	$getstate->execute();
	$state = $getstate->fetch();

	if ($state['open'] == 0) {
		$success = [0,'فترة التعديل و التسجيل مغلقة الآن، يرجى المحاولة لاحقاً'];
	}

} catch (PDOException $ex) {
	$state['open']=0;
	$success = [0,'فترة التعديل و التسجيل مغلقة الآن، يرجى المحاولة لاحقاً'];
}

if (isset($_POST['update'])) {
	if ($state['open'] == 0){
		$success = [0,'فترة التعديل مغلقة الآن'];
	}
	else {
		$success = updatequeue($_POST);
	}
}

if (isset($_POST['add'])) {
	if ($state['open'] == 0) {
		$success = [0,'فترة الإضافة مغلقة الآن'];
	}
	else {
		$success = add2queue($_POST);
	}
}

$user = null;
?>
<!DOCTYPE html>
<html lang="en">
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
	  <!-- Jquery-ui style -->
	  <link rel="stylesheet" href="css/custom-theme/jquery-ui-1.10.0.custom.css">
	  <link rel="stylesheet" href="css/custom-theme/jquery.ui.1.10.0.ie.css">
	  <!-- Custom style -->
	  <link rel="stylesheet" href="css/default.css">
	  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	  <!--[if lt IE 9]>
	  	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	  	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	  <![endif]-->
	</head>
	<body>
	<div class="container">
		<div class="header clearfix" style="padding-bottom: 5px;">
			<?php
			if (isset($_SESSION['login'])) {
			?>
			<nav>
			  <ul class="nav nav-pills pull-left">
				<li role="presentation"><a href="control.php">لوحة التحكم</a></li>
			  </ul>
			</nav>
			<?php
			}
			?>
			<a class="navbar-brand navbar-right" style="padding: 0 0 0 25px;">
				<img alt="Brand" src="M-Logo.png" style="width: 45px; margin-right: 15px;">
			</a>
			<h3 class="navbar-text navbar-right hidden-xs" style="margin-top: 3px;">مأتم الإمام علي (ع)</h3>
		</div>
		<div class="jumbotron hidden">
			<h1>تسجيل تعهد حضور إجتماع الجمعية العمومية - 1439هـ<br><small>مأتم الإمام علي(ع) -قرية بوري- <?php echo date("Y"); ?>م</small></h1>
			<p class="lead">تهدف الاستمارة إلى تحديث وحصر بيانات العضوية لمأتم الإمام علي(ع)- قرية بوري للاستفادة منها في المجالات الإدارية والفنية اللازمة مع جزيل شكرنا لتعاونكم معنا</p>
		</div>
		<div class="jumbotron hidden">
			<h1>سجل الناخبين الأولي لإنتخابات مجلس إدارة مأتم الإمام علي (ع)<br> <small> للدورة الحادية عشر للعامين <br> 1442-1443 هـ</small> </h1>
			<p class="lead">تهدف الاستمارة إلى تحديث وحصر بيانات العضوية لمأتم الإمام علي(ع)- قرية بوري للاستفادة منها في المجالات الإدارية والفنية اللازمة مع جزيل شكرنا لتعاونكم معنا</p>
		</div>
		<div class="jumbotron">
			<h1>استمارة تحديث بيانات العضوية<br><small>مأتم الإمام علي(ع) -قرية بوري- <?php echo date("Y"); ?>م</small></h1>
			<p class="lead">تهدف الاستمارة إلى تحديث وحصر بيانات العضوية لمأتم الإمام علي(ع)- قرية بوري للاستفادة منها في المجالات الإدارية والفنية اللازمة مع جزيل شكرنا لتعاونكم معنا</p>
		</div>
		<div class="row marketing" style="margin-top: 0;">
			<div class="col-md-12">
			<?php
			if (isset($success)) {
			?>
			<div class="alert alert-<?php if ($success[0]) echo 'success'; else echo 'danger'; ?> alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<?php echo $success[1]; ?>
			</div>
			<?php
			}
			if (!isset($_POST['check']) && !isset($_POST['edit']) && !isset($_POST['new'])) {
			?>
			<div class="well center-block" style="max-width: 400px;">
				<form role="form" method="POST">
					<button type="submit" name="edit" class="btn btn-warning btn-lg btn-block" <?php if($state['open'] == 0) echo "disabled"; ?>>التسجيل</button>
					<button type="submit" name="edit" class="btn btn-warning btn-lg btn-block hidden" <?php if($state['open'] == 0) echo "disabled"; ?>>تسجيل الدخول</button>
                    <button type="submit" name="new" class="btn btn-primary btn-lg btn-block" <?php if($state['open'] == 0) echo "disabled"; ?>>طلب تسجيل عضوية جديد</button>
				</form>
			</div>
			<?php
			} else if (isset($_POST['new'])) {
				print_new_form();
			} else if (isset($_POST['edit'])) {
			?>
			<div class="well center-block" style="max-width: 400px;">
				<form role="form" method="POST">
					<div class="form-group" id="div" >
						<input type="text" class="form-control input-lg" name="CPR" id="CPR" placeholder="الرقم الشخصي" maxlength="9" onkeyup="checkCPR(this.value)" autocomplete="off" required>
						<span class="" id="span" aria-hidden="true"></span>
				
					</div>
					<input type="hidden" name="check_vot" value="1" />
					<button type="submit" id="check" name="check" class="btn btn-primary btn-lg btn-block">التالي</button>
					<button type="submit" id="check_reg" name="check_reg" class="btn btn-primary btn-lg btn-block hidden" disabled>التالي</button>
				</form>
			</div>
			<?php
			} else if (isset($_POST['check']) || isset($_POST['check_reg'])) {
				$print = true;
				if (isset($_POST['check']) || isset($_POST['check_reg']))
					try
					{
						if (findUser($_POST['CPR'],'queue'))
							$user = $db->prepare("SELECT * FROM queue WHERE CPR=".$_POST['CPR']);
						else
							$user = $db->prepare("SELECT * FROM user WHERE CPR=".$_POST['CPR']);
						$user ->execute();
						$user = $user->fetch();
					} catch (PDOException $ex) {
						$print = false;
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				حدث خطأ في جلب البيانات يرجى المحاولة مرة أخرى
			</div>
			<?php
					}// catch end
					if (!isset($user) || $user == null) {
						$print = false;
						?>
						<div class="alert alert-danger alert-dismissible" role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							حدث خطأ في جلب البيانات يرجى المحاولة مرة أخرى
						</div>
						<?php
						$print = false;
					}
					if (isset($_POST['check_reg'])) {
    					$result = registerUserToMeeting($user);
    					if ($result == true){
    						echo "<script>alert('تم تسجيلك ضمن قائمة المتعهدين لحضور إجتماع الجمعية العمومية - 1439هـ');</script>";
    					} else if (!is_null($result) && $result == false) {
    					    echo "<script>alert('لقد تم تسجيلك ضمن قائمة المتعهدين مسبقاً');</script>";
    					} else {
    					    echo "<script>alert('حدث خطأ في جلب البيانات يرجى المحاولة مرة');</script>";
    					}
    					echo'<script>window.location="http://member.memamali.com/";</script>';
    					end();
					    $print = false;
					}
					if (isset($_POST['check_vot'])) {
    					$result = registerUserToVoting($user);
    					if ($result == true){
    					    $alert = 'شكراً '. $user['name'] .'،\nاسمك مسجل ضمن سجل الناخبين.';
    						echo "<script>alert(`$alert`);</script>";
    					}
					}
				if ($print) {
					getFamily($_POST['CPR']);
		?>
		<div class="alert alert-success alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				مرحباً بك <strong><?php echo getName($_POST['CPR']); ?></strong>
		</div>
		<div class="row marketing" style="margin-top: 0;">
			<div class="col-md-12">
				<h3 class="control-label" style="margin-top: 15px;">شجرة العائلة</h3>
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
								<th class="col-xs-11">الإسم</th>
							</tr>
						</thead>
						<tbody>
						<?php
						for ($i=0;$i<count($parents["CPR"]);$i++) {
						?>
							<tr>
								<td><?php echo $i+1 ?></td>
								<td><?php echo $parents["name"][$i] ?></td>
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
								<th class="col-xs-11">الإسم</th>
							</tr>
						</thead>
						<tbody>
						<?php
						for ($i=0;$i<count($brothers["CPR"]);$i++) {
						?>
							<tr>
								<td><?php echo $i+1 ?></td>
								<td><?php echo $brothers["name"][$i] ?></td>
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
						for ($i=0;$i<count($childs["CPR"]);$i++) {
						?>
							<tr>
								<td><?php echo $i+1 ?></td>
								<td><?php echo $childs["name"][$i] ?></td>
								<td><button class="btn btn-default"><?php echo $childs["CPR"][$i] ?></td>
							</tr>
						<?php
						}
						?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
					print_user_form($user);
				}
			}
			?>
			</div>
		</div>
		<?php if (isset($_POST['check'])) {
		?>
		<div class="jumbotron">
			<h2>شاكرين لكم حسن تعاونكم معنا، وتفضلوا بقبول فائق التحيات</h2>
		</div>
		<?php
		}
		?>
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
		<footer class="footer">
			<p>مأتم الإمام علي (ع)</p>
		</footer>
	</div> <!-- /container -->
	<script src="js/jquery-2.2.3.min.js"></script>
	<script src="js/jquery-ui.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="default.js?version=<?php echo time(); ?>"></script>
	</body>
</html>