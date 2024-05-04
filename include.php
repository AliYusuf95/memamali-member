<?php
function registerUserToMeeting($user) {
	global $db;

	if(!isset($user) || !isset($user["CPR"]) || !isset($user["dob"]) || (new DateTime($user["dob"]))->diff(new DateTime('today'))->y < 18){
		return null;
	}

	try {
		$insert = $db->prepare("INSERT INTO `meeting`(`cpr`) VALUES (?)");
		$insert -> execute(array($user["CPR"]));
		return true;
	}catch (PDOException $ex) {
		return false;
	}
}

function registerUserToVoting($user) {
	global $db;

	if(!isset($user) || !isset($user["CPR"])){
		return false;
	}
	
	try {
	    $sql = $db->prepare("SELECT * FROM user WHERE cpr=?");
	    $sql -> execute(array($user["CPR"]));
		if($sql->rowCount() != 1) {
	        return false;
	    }
	    // prevent user to register twice
	    $votingUser = $sql ->fetch(PDO::FETCH_ASSOC);
	    if($votingUser['register'] == true) {
	        return true;
	    }
	} catch (PDOException $ex) {
	    return false;
	}

	try {
		$update = $db->prepare("UPDATE `user` SET `register`=1 WHERE cpr=?");
		$update -> execute(array($user["CPR"]));
		return true;
	} catch (PDOException $ex) {
	    return false;
	}
	
	return false;
}

function usersInVoting() {
	global $db;
	try {
		$usersCount = $db->prepare("SELECT * FROM user");
		$usersCount -> execute();
		return $usersCount->rowCount();
	} catch (PDOException $ex) {
		return 0;
	}
}

function usersRegisteredoVoting() {
	global $db;
	try {
		$usersCount = $db->prepare("SELECT * FROM user WHERE `register`=1");
		$usersCount -> execute();
		return $usersCount->rowCount();
	} catch (PDOException $ex) {
		return 0;
	}
}

function usersInMeetingCount() {
	global $db;
	try {
		$usersCount = $db->prepare("SELECT * FROM meeting");
		$usersCount -> execute();
		return $usersCount->rowCount();
	} catch (PDOException $ex) {
		return 0;
	}
}


function getLastEdit($CPR)
{
	global $db;
	try {
		$sql = $db->prepare("SELECT lastEdit FROM user WHERE CPR=?");
		$sql -> execute(array($CPR));
		$user = $sql ->fetch(PDO::FETCH_ASSOC);
		return $user['lastEdit'];
	}catch (PDOException $ex) {
		return 'غير موجود ..';
	}
}

function getApprovedBy($CPR)
{
	global $db;
	try {
		$sql = $db->prepare("SELECT approvedBy FROM user WHERE CPR=?");
		$sql -> execute(array($CPR));
		$user = $sql ->fetch(PDO::FETCH_ASSOC);
		return $user['approvedBy'];
	}catch (PDOException $ex) {
		return 'غير موجود ..';
	}
}

function getActive($CPR)
{
	global $db;
	try {
		$sql = $db->prepare("SELECT active FROM user WHERE CPR=?");
		$sql -> execute(array($CPR));
		$user = $sql ->fetch(PDO::FETCH_ASSOC);
		return $user['active'];
	}catch (PDOException $ex) {
		return '0';
	}
}

function getFamily($CPR)
{
	global $db,$parents,$brothers,$childs;
	try {
		$get = $db->prepare("SELECT parent FROM father WHERE child=?");
		$get -> execute(array($CPR));
		while ($get->rowCount()==1)
		{
			$temp = $get ->fetch(PDO::FETCH_ASSOC);
			$parents["CPR"][] = $temp['parent'];
			$parents["name"][] = getName($temp['parent']);
			$pCPR=$temp['parent'];
			$get -> execute(array($pCPR));
		}
		if (isset($parents["CPR"][0]))
		{
			$get = $db->prepare("SELECT child FROM father WHERE parent=? AND child<>?");
			$get -> execute(array($parents["CPR"][0],$CPR));
			while ($temp = $get ->fetch(PDO::FETCH_ASSOC))
			{
				$brothers["CPR"][] = $temp['child'];
				$brothers["name"][] = getName($temp['child']);
			}
		}
		$get = $db->prepare("SELECT child FROM father WHERE parent=?");
		$get -> execute(array($CPR));
		if ($get->rowCount()>0)
		while ($temp = $get ->fetch(PDO::FETCH_ASSOC))
		{
			$childs["CPR"][] = $temp['child'];
			$childs["name"][] = getName($temp['child']);
		}
	}catch (PDOException $ex) {
		echo "حدث خطأ خلال جلب البيانات ..";
	}
}

function getName($CPR)
{
	global $db;
	try {
		$sql = $db->prepare("SELECT name FROM user WHERE CPR=?");
		$sql -> execute(array($CPR));
		$user = $sql ->fetch(PDO::FETCH_ASSOC);
		return $user['name'];
	}catch (PDOException $ex) {
		return 'غير موجود ..';
	}
}

function findUser($CPR,$table)
{
	global $db;
	try {
		$checkuser = $db->prepare("SELECT CPR FROM ".$table." WHERE CPR=?");
		$checkuser -> execute(array($CPR));
		if ($checkuser->rowCount()==0)
			return false;
		else
			return true;
	}catch (PDOException $ex) {
		return false;
	}
}

function add2queue($user)
{
	global $db,$success;
	$user['hobby'] = implode(", ", $user['hobby']);
	unset($user['add']);
	
	if (isset($_POST['add']) && findUser($user['CPR'], 'user')) {
	    return $success = [0,'المستخدم مسجل في قاعدة البيانات مسبقاً .. يمكنك تسجيل الدخول للتعديل على البيانات'];
	}
	
	$ok = check($user);
	if (!$ok)
		return $success = [0,'بعض البيانات أدخلت بشكل غير صحيح .. يرجى المحاولة مرة أخرى'];
	else
	try {
		$user = array_values($user);
		$insert = $db->prepare("INSERT INTO queue VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,STR_TO_DATE(\"".date("d-m-Y")."\",\"%d-%m-%Y\"))");
		$insert -> execute($user);
		$success = [1,'تم إضافة البيانات بنجاح'];
	}catch (PDOException $ex) {
		$success = [0,'فشل في إضافة البيانات .. يرجى المحاولة مرة أخرى'];
	}
	return $success;
}

function updatequeue($user)
{
	global $db,$success;
	$user['hobby'] = implode(", ", (array)$user['hobby']);
	unset($user['update']);
	$CPR = $user['CPR'];
	
	$ok = check($user);
	if (!$ok)
		return $success = [0,'بعض البيانات أدخلت بشكل غير صحيح .. يرجى المحاولة مرة أخرى'];
	else
	try {
		if (findUser($CPR,'queue'))
		{
			unset($user['CPR']);
			$user['CPR'] = $CPR;
			$update = $db->prepare("UPDATE `queue` SET `name`=?,`dob`=?,`phone`=?,`edLevel`=?,`major`=?,`TC`=?,`emState`=?,`employer`=?,`jobName`=?,`maState`=?,`kidNum`=?,`involved`=?,`involvedName`=?,`hobby`=?,`otherHobby`=?,`boys`=?, `lastEdit`=STR_TO_DATE(\"".date("d-m-Y")."\",\"%d-%m-%Y\") WHERE `CPR`=?");
		}
		else
			$update = $db->prepare("INSERT INTO queue VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,STR_TO_DATE(\"".date("d-m-Y")."\",\"%d-%m-%Y\"))");
		$user = array_values($user);
		$update -> execute($user);
		return $success = [1,'تم تحديث البيانات بنجاح'];
	}catch (PDOException $ex) {
		return $success = [0,'فشل تحديث البيانات .. يرجى المحاولة مرة أخرى'];
	}
}

function add2user($user)
{
	global $db,$success;
	
	if (!isset($user['active']))
	{
		$user['active'] = 1;
		if ($user['active']==1)
			$user['active_comment'] = '';
	}
	else
	{
		$active = $user['active'];
		$active_comment = $user['active_comment'];
		unset($user['active']);
		unset($user['active_comment']);
		$user['active'] = $active;
		$user['active_comment'] = $active_comment;
	}
	
	$ok = check($user);
	if (!$ok)
		return $success = [0,'بعض البيانات أدخلت بشكل غير صحيح .. يرجى المحاولة مرة أخرى'];
	else
	try {
		//var_dump($user);
		$user = array_values($user);
		$insert = $db->prepare("INSERT INTO user VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,STR_TO_DATE(\"".date("d-m-Y")."\",\"%d-%m-%Y\"),'".$_SESSION['login']."')");
		$insert -> execute($user);
		$success = [1,'تم إضافة البيانات بنجاح'];
	}catch (PDOException $ex) {
		$success = [0,'فشل في إضافة البيانات .. يرجى المحاولة مرة أخرى'];
	}
	return $success;
}

function updateuser($user)
{
	global $db,$success;
	//$user['hobby'] = implode(", ", $user['hobby']);
	if (!isset($user['active']))
	{
		$user['active'] = 1;
		if ($user['active']==1)
			$user['active_comment'] = '';
	}
	else
	{
		$active = $user['active'];
		$active_comment = $user['active_comment'];
		unset($user['active']);
		unset($user['active_comment']);
		$user['active'] = $active;
		$user['active_comment'] = $active_comment;
	}
	
	$CPR = $user['CPR'];
	unset($user['CPR']);
	
	$user['CPR'] = $CPR;
	$ok = check($user);
	if (!$ok)
		return $success = [0,'بعض البيانات أدخلت بشكل غير صحيح .. يرجى المحاولة مرة أخرى'];
	else
	try {
		$update = $db->prepare("UPDATE `user` SET `name`=?, `dob`=?,`phone`=?,`edLevel`=?,`major`=?,`TC`=?,`emState`=?,`employer`=?,`jobName`=?,`maState`=?,`kidNum`=?,`involved`=?,`involvedName`=?,`hobby`=?,`otherHobby`=?,`boys`=?,`active`=?,`active_comment`=?, `lastEdit`=STR_TO_DATE(\"".date("d-m-Y")."\",\"%d-%m-%Y\"), `approvedBy`='".$_SESSION['login']."' WHERE CPR=?");
		$user = array_values($user);
		$update -> execute($user);
		return $success = [1,'تم تحديث البيانات بنجاح'];
	}catch (PDOException $ex) {
		return $success = [0,'فشل تحديث البيانات .. يرجى المحاولة مرة أخرى'];
	}
}

function check($user)
{
	$pattern = "/^[{Arabic} ]$/";
	if (!isset($user['name']) || preg_match($pattern,$user['name']) != 0 || trim($user['name'])== '')
		return false;

	$pattern = "/^[0-9]{9}$/";
	if (!isset($user['CPR']) || preg_match($pattern,$user['CPR']) == 0)
		return false;

	$pattern = "/[a-zA-Z|{Arabic}]/";
	if (!isset($user['phone']) || preg_match($pattern,$user['phone']) || strlen(trim($user['phone'])<8) )
		return false;

	if (!isset($user['edLevel']) || trim($user['edLevel'])=='')
		return false;

	//$user['major']
	//$user['TC']

	//if (trim($user['emState'])=='')
	//	return false;

	//$user['employer']
	//$user['jobName']

	if (!isset($user['maState']) || trim($user['maState'])=='')
		return false;

	$pattern = "/^[0-9]+$/";
	if (!isset($user['kidNum']) || !preg_match($pattern,$user['kidNum']))
		return false;

	if (!isset($user['involved']) || trim($user['involved'])=='')
		return false;

	//$user['involvedName']

	//if (trim($user['hobby'])=='')
	//	return false;
	//$user['otherHobby']
	//$user['boys']
	return true;
}

function ExportUserToExcel($where)
	{
		global $db;
		/** Include PHPExcel */
		require_once ('Classes/PHPExcel.php');

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set document properties
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
									 ->setLastModifiedBy("Maarten Balliauw")
									 ->setTitle("Office 2007 XLSX Test Document")
									 ->setSubject("Office 2007 XLSX Test Document")
									 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
									 ->setKeywords("office 2007 openxml php")
									 ->setCategory("Test result file");


		// Add some data
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()
					->setRightToLeft(true)
					->freezePane('A2');

		try{
			$sql = $db->prepare("SELECT * FROM user $where ORDER BY dob");
			$sql->execute();
			$num_rows = $sql->rowCount();
			$num_cols = $sql->columnCount();
			if($num_rows >= 1)
			{
				$range = 'B1:B'.$num_rows;
				$objPHPExcel->getActiveSheet()
					->getStyle('A1:U1')
					->getFill()
					->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
					->getStartColor()
					->setARGB('e3e3e3');
                $objPHPExcel->getActiveSheet()
                    ->getStyle('C:C')
                    ->getNumberFormat()
                    ->setFormatCode('yyyy-mm-dd');
				$row = $sql->fetch(PDO::FETCH_ASSOC);
				$col_name = array_keys($row);
				$arabic_name = array('الاسم','الرقم الشخصي','تاريخ الميلاد','الهاتف','المستوى التعليمي','التخصص','نوع التخصص','الحالة الوظيفية','مكان الوظيفة','المسمى الوظيفي','الحالة الإجتماعية','عدد الأولاد','مرتبط بمؤسسة','اسماء المؤسسات','مهارات','هوايات','الأولاد الذكور','فعّال','تعليق','إعتمد بتاريخ','إعتمد من');
				for ($c=0;$c<$num_cols;$c++)
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c,1,$arabic_name[$c]);
				for ($r=2;$r<=$num_rows+1;$r++)
				{
					for ($c=0;$c<$num_cols;$c++)
							$objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow($c,$r,$row[$col_name[$c]]);
					$row = $sql->fetch(PDO::FETCH_ASSOC);
				}
				foreach (range(0, 1) as $col) {
					$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(true);                
				}
			}
		}catch(PDOException $ex) {
			//user friendly message
			die ();
		}
		
		// Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle('الأسماء');


		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);


		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="قائمة الأسماء '.date("d-m-Y").'.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}

function InportExcel($table)
	{
		global $db,$success;
		/** Include PHPExcel */
		require_once ('Classes/PHPExcel.php');
		require_once ('Classes/PHPExcel/IOFactory.php');
		
		$target_file = basename($_FILES["file"]["name"]);
		$FileType = pathinfo($target_file,PATHINFO_EXTENSION);
		$uploadOk = 1;
		// Allow certain file formats
		$allowed =  array('xsl','xlsx');
		if(!in_array($FileType,$allowed) ) {
			$uploadOk = 0;
		}
		if ($uploadOk == 0) {
			$success = [0,'صيغة الملف غير صحيحة'];
		// if everything is ok, try to upload file
		} else {
			if (move_uploaded_file($_FILES["file"]["tmp_name"], "Names.".$FileType)) {
				/**  Insert in Database **/
				$path = "Names.".$FileType;
				$ok = true;
				try{
					$delete = $db->prepare("DELETE FROM $table");
					$insert = $db->prepare("INSERT INTO user VALUES (?,?,?,?,?,?,?,IFNULL(?,'عاطل'),?,?,?,IFNULL(?,0),?,?,IFNULL(?,'لايوجد'),?,?,IFNULL(?,1),?,?,?)");
					$db->beginTransaction();
					$delete->execute();
					$objPHPExcel = PHPExcel_IOFactory::load($path);
					foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
						$worksheetTitle     = $worksheet->getTitle();
						$highestRow         = $worksheet->getHighestRow(); // e.g. 10
						$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
						$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
						$nrColumns = ord($highestColumn) - 64;
						for ($row = 2; $row <= $highestRow && $ok; ++ $row) {
							$val=array();
							for ($col = 0; $col < $highestColumnIndex && $ok; ++ $col) {
								$cell = $worksheet->getCellByColumnAndRow($col, $row);
								$val[] = PHPExcel_Style_NumberFormat::toFormattedString( $cell->getValue(),
												$objPHPExcel->getCellXfByIndex( $cell->getXfIndex() )->getNumberFormat()->getFormatCode());
							}
							$ms = $insert->execute($val);
						}
					}
				}catch(PDOException $ex) {
					$ok = false;
					$error = $ex;
				}
				
				// Check if there is error while inserting data
				if ($ok)
				{
					$db->commit();
					unlink($path);
					$success = [1,'تم تحديث قاعدة البيانات بنجاح'];
				}
				else
				{
					$db->rollBack();
					$success = [0,'الملف المرفق يحتوي على بيانات خاطئة أو بيانات مطلوبة ناقصة'];
				}
				
			} else {
				// cant move file
				$success = [0,'فشل تحديث قاعدة البيانات'];
			}
		}
	}

function print_user_form($user)
{
	extract($user);
	$hobbies = array('شئون دينية','مالية وحسابات','خدمات وصيانة','شئون ثقافية','شئون مكتبية','سكرتارية','علاقات عامة','إعلام','طباخ','مشاريع واستثمار','إدارة فرق عمل',',');
	$str = trim(str_replace($hobbies,'',$hobby));
	?>
			<div class="row marketing" style="margin-top: 0px;">
			<div class="col-md-12">
			<form role="form" method="POST" onsubmit="return checkForm()">
				<div class="col-sm-12">
					<h4 class="text-danger">معلومات مطلوبة *</h4>
					<h2>المعلومات الشخصية</h2>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label" for="name">اسم العضو: الرجاء كتابة الإسم ثلاثيا على الأقل</label><span class="text-danger" style="font-size:20px;"> *</span>
					</div>
					<div class="col-sm-4">
						<input type="text" class="form-control" name="name"  id="name" <?php echo "value='$name'"; ?>>
					</div>
				</div>
				<?php
				if (isset($_SESSION['login']) && basename($_SERVER['PHP_SELF'], '.php')!= 'index')
				{
					if (!isset($active))
						$active=1;
				?>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label" for="active">عضوية مفعّلة</label><span class="text-danger" style="font-size:20px;"> *</span>
					</div>
					<div class="col-sm-12">
						<div class="radio">
							<label>
								<input type="radio" name="active" id="active1" value="1" <?php  if($active=='1') echo "checked"; ?>>
								مفعّلة
							</label>
						</div>
						<div class="radio">
							<label>
								<input type="radio" name="active" id="active2" value="0" <?php  if($active=='0') echo "checked"; ?>>
								غير مفعّلة
							</label>
						</div>
					</div>
					<div class="col-sm-4">
						<input type="text" class="form-control" name="active_comment"  id="active_comment" <?php if(isset($active_comment)) echo "value='$active_comment'"; ?> >
					</div>
				</div>
				<?php
				}
				?>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label" for="CPR">الرقم الشخصي: تأكد أنه يحتوي على 9 أرقام</label><span class="text-danger" style="font-size:20px;"> *</span>
					</div>
					<div class="col-sm-4"> 
					<?php
					if (isset($_SESSION['login']) && basename($_SERVER['PHP_SELF'], '.php')!= 'index')
					{
					?>
						<input type="text" class="form-control" name="CPR" id="CPR" maxlength="9" <?php echo "value='$CPR'"; ?>>
						<input type='hidden' id='oldCPR' name='oldCPR' value='<?php echo "$CPR"; ?>'>
					<?php
					}
					else
					{
					?>
						<input type="text" class="form-control" id="CPR" maxlength="9" <?php echo "value='$CPR' disabled"; ?>>
					<?php echo "<input type='hidden' name='CPR' value='$CPR'>"; 
					}	
					?>
					</div>
				</div>
                <div class="form-group">
                    <div class="col-sm-12">
                        <label class="control-label" for="name">تاريخ الميلاد</label><span class="text-danger" style="font-size:20px;"> *</span>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="dob"  id="dob" <?php echo "value='$dob'"; ?> readonly>
                    </div>
                </div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">رقم الهاتف: تأكد أنه يحتوي على 8 أرقام، ويمكنك وضع - ثم إضافة رقم هاتف آخر إن وجد </label><span class="text-danger" style="font-size:20px;"> *</span>
					</div>
					<div class="col-sm-4"> 
						<input type="text" class="form-control" name="phone" id="phone" <?php echo "value='$phone'"; ?>>
					</div>
				</div>
				<div class="col-sm-12">
					<h2>المستوى الأكاديمي</h2>
				</div>
				<div class="col-sm-12">
					<label class="control-label">المستوى التعليمي</label><span class="text-danger" style="font-size:20px;"> *</span>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="edLevel" id="edLevel1" value="ابتدائي" <?php  if($edLevel=='ابتدائي') echo "checked"; ?>>
							ابتدائي
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="edLevel" id="edLevel2" value="إعدادي" <?php  if($edLevel=='إعدادي') echo "checked"; ?>>
							إعدادي
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="edLevel" id="edLevel3" value="ثانوي" <?php  if($edLevel=='ثانوي') echo "checked"; ?>>
							ثانوي
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="edLevel" id="edLevel4" value="جامعي" <?php  if($edLevel=='جامعي') echo "checked"; ?>>
							جامعي
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="edLevel" id="edLevel5" value="غير ذلك" <?php  if($edLevel=='غير ذلك') echo "checked"; ?>>
							غير ذلك
						</label>
					 </div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">التخصص العلمي</label>
					</div>
					<div class="col-sm-4"> 
						<input type="text" class="form-control" name="major" id="major" <?php  echo "value='$major'"; ?>>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">دورات تدريبية أخرى</label>
					</div>
					<div class="col-sm-7"> 
						<textarea class="form-control" name="TC" id="TC" rows="4"><?php  echo $TC; ?></textarea>
					</div>
				</div>
				<div class="col-sm-12">
					<h2>الحالة الوظيفية</h2>
				</div>
				<div class="col-sm-12">
					<label class="control-label">الحالة الوظيفة</label><span class="text-danger" style="font-size:20px;"> *</span>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="emState" id="emState1" value="عامل" <?php  if($emState=='عامل') echo "checked"; ?>>
							عامل
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="emState" id="emState2" value="عاطل" <?php  if($emState=='عاطل') echo "checked"; ?>>
							عاطل
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="emState" id="emState3" value="متقاعد" <?php  if($emState=='متقاعد') echo "checked"; ?>>
							متقاعد
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="emState" id="emState4" value="طالب" <?php  if($emState=='طالب') echo "checked"; ?>>
							طالب
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="emState" id="emState5" value="<?php  if($emState!='عامل' && $emState!='متقاعد' && $emState!='طالب') echo $emState; ?>" <?php  if($emState!='عامل' && $emState!='متقاعد' && $emState!='طالب') echo "checked"; ?>>
							أخرى
						</label>
					 </div>
				</div>
				<div class="form-group">
					<div class="col-sm-4"> 
						<input type="" class="form-control" onkeyup="if(document.getElementById('edLevel5').checked)document.getElementById('edLevel5').value=this.value" <?php  if($emState!='عامل' && $emState!='متقاعد' && $emState!='طالب') echo "value='$emState'"; ?>>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">في حالة العمل: اذكر جهة العمل</label>
					</div>
					<div class="col-sm-4"> 
						<input type="text" class="form-control" name="employer" id="employer" <?php  echo "value='$employer'"; ?>>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">في حالة العمل: اذكر المسمى الوظيفي- نوع الوظيفة</label>
					</div>
					<div class="col-sm-4"> 
						<input type="" class="form-control" name="jobName" id="jobName" <?php  echo "value='$jobName'"; ?>>
					</div>
				</div>
				<div class="col-sm-12">
					<h2>الوضع الإجتماعي </h2>
				</div>
				<div class="col-sm-12">
					<label class="control-label">الحالة الإجتماعية:</label><span class="text-danger" style="font-size:20px;"> *</span>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="maState" id="maState1" value="عازب" <?php  if($maState=='عازب') echo "checked"; ?>>
							عازب
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="maState" id="maState2" value="متزوج" <?php  if($maState=='متزوج') echo "checked"; ?>>
							متزوج
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="maState" id="maState3" value="مطلق" <?php  if($maState=='مطلق') echo "checked"; ?>>
							مطلق
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="maState" id="maState4" value="أرمل" <?php  if($maState=='أرمل') echo "checked"; ?>>
							أرمل
						</label>
					 </div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">عدد الأولاد: ذكور وإناث- إن وجد</label>
					</div>
					<div class="col-sm-4"> 
						<input type="number" class="form-control" name="kidNum" id="kidNum" <?php  echo "value='$kidNum'"; ?>>
					</div>
				</div>
				<div class="col-sm-12">
					<h2>معلومات عامة: <small>(للاستفادة منها في جميع أنشطة المأتم)</small></h2>
				</div>
				<div class="col-sm-12">
					<label class="control-label">هل سبق لك العمل في أي من المؤسسات الأخرى</label><span class="text-danger" style="font-size:20px;"> *</span>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="involved" id="involved1" value="نعم" <?php  if($involved=='نعم') echo "checked"; ?>>
							نعم
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="involved" id="involved2" value="لا" <?php  if($involved=='لا') echo "checked"; ?>>
							لا
						</label>
					 </div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">في حالة الإجابة (نعم): اذكر المؤسسات التي عملت بها</label>
					</div>
					<div class="col-sm-7"> 
						<textarea class="form-control" name="involvedName" id="involvedName" rows="4"><?php  echo $involvedName; ?></textarea>
					</div>
				</div>
				<div class="col-sm-12">
					<label class="control-label">اذكر المهارات الشخصية التي تتمتع بها</label><span class="text-danger" style="font-size:20px;"> *</span>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby1" value="شئون دينية" <?php  if(strpos($hobby,'شئون دينية') !== false) echo "checked"; ?>>
							شئون دينية
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby2" value="مالية وحسابات" <?php  if(strpos($hobby,'مالية وحسابات') !== false) echo "checked"; ?>>
							مالية وحسابات
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby3" value="خدمات وصيانة" <?php  if(strpos($hobby,'خدمات وصيانة') !== false) echo "checked"; ?>>
							خدمات وصيانة
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby4" value="شئون ثقافية" <?php  if(strpos($hobby,'شئون ثقافية') !== false) echo "checked"; ?>>
							شئون ثقافية
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby5" value="شئون مكتبية" <?php  if(strpos($hobby,'شئون مكتبية') !== false) echo "checked"; ?>>
							شئون مكتبية
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby6" value="سكرتارية" <?php  if(strpos($hobby,'سكرتارية') !== false) echo "checked"; ?>>
							سكرتارية
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby7" value="علاقات عامة" <?php  if(strpos($hobby,'علاقات عامة') !== false) echo "checked"; ?>>
							علاقات عامة
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby8" value="إعلام" <?php  if(strpos($hobby,'إعلام') !== false) echo "checked"; ?>>
							إعلام
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby9" value="طباخ" <?php  if(strpos($hobby,'طباخ') !== false) echo "checked"; ?>>
							طباخ
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby10" value="مشاريع واستثمار" <?php  if(strpos($hobby,'مشاريع واستثمار') !== false) echo "checked"; ?>>
							مشاريع واستثمار
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby11" value="إدارة فرق عمل" <?php  if(strpos($hobby,'خدمات وصيانة') !== false) echo "checked"; ?>>
							إدارة فرق عمل
						</label>
					</div>
				</div>
				<div class="col-sm-1">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby12" value="<?php  if ($str) echo $str; ?>" <?php  if ($str) echo "checked"; ?>>
							أخرى:
						</label>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-4"> 
						<input type="" class="form-control" onkeyup="document.getElementById('hobby12').value=this.value" <?php  if ($str) echo "value='$str'"; ?> >
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">اذكر الهوايات والاهتمامات التي تعنيك</label>
					</div>
					<div class="col-sm-7"> 
						<textarea class="form-control" name="otherHobby" id="otherHobby" rows="4"><?php  echo $otherHobby; ?></textarea>
					</div>
				</div>
				<div class="col-sm-12">
					<h2>معلومات الأبناء</h2>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">معلومات الأبناء الذكور</label>
						<span id="helpBlock" class="help-block">الصيغة المطلوبة: إسم الولد - الرقم الشخصي ، في حالة أكثر من ولد:معلومات كل ولد توضع في سطر خاص جديد</span>
					</div>
					<div class="col-sm-7"> 
						<textarea style="margin-bottom: 15px;" class="form-control" name="boys" id="boys" rows="4"><?php  echo $boys; ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-4 col-sm-offset-4">
							<button class="btn btn-success btn-block btn-lg" type="submit" name="update">إرسال</button>
							<button class="btn btn-danger btn-block btn-lg" type="reset" onclick="window.location=window.location" >إلغاء</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<?php
}

function print_new_form()
{
	?>
	<div class="row marketing" style="margin-top: 0;">
		<div class="col-md-12">
			<form role="form" method="POST" onsubmit="return checkForm()">
				<div class="col-sm-12">
					<h4 class="text-danger">معلومات مطلوبة *</h4>
					<h2>المعلومات الشخصية</h2>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label" for="name">اسم العضو: الرجاء كتابة الإسم ثلاثيا على الأقل</label><span class="text-danger" style="font-size:20px;"> *</span>
					</div>
					<div class="col-sm-4">
						<input type="text" class="form-control" name="name"  id="name" >
					</div>
				</div>
				<?php
				if (isset($_SESSION['login']) && basename($_SERVER['PHP_SELF'], '.php')!= 'index')
				{
				?>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label" for="active">عضوية مفعّلة</label><span class="text-danger" style="font-size:20px;"> *</span>
					</div>
					<div class="col-sm-12">
						<div class="radio">
							<label>
								<input type="radio" name="active" id="active1" value="1" checked>
								مفعّلة
							</label>
						</div>
						<div class="radio">
							<label>
								<input type="radio" name="active" id="active2" value="0">
								غير مفعّلة
							</label>
						</div>
					</div>
					<div class="col-sm-4">
						<input type="text" class="form-control" name="active_comment"  id="active_comment" >
					</div>
				</div>
				<?php
				}
				?>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label" for="CPR">الرقم الشخصي: تأكد أنه يحتوي على 9 أرقام</label><span class="text-danger" style="font-size:20px;"> *</span>
					</div>
					<div class="col-sm-4"> 
						<input type="text" class="form-control" name="CPR" id="CPR" maxlength="9">
						
					</div>
				</div>
                <div class="form-group">
                    <div class="col-sm-12">
                        <label class="control-label" for="name">تاريخ الميلاد</label><span class="text-danger" style="font-size:20px;"> *</span>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="dob"  id="dob" readonly>
                    </div>
                </div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">رقم الهاتف: تأكد أنه يحتوي على 8 أرقام، ويمكنك وضع - ثم إضافة رقم هاتف آخر إن وجد </label><span class="text-danger" style="font-size:20px;"> *</span>
					</div>
					<div class="col-sm-4"> 
						<input type="text" class="form-control" name="phone" id="phone" >
					</div>
				</div>
				<div class="col-sm-12">
					<h2>المستوى الأكاديمي</h2>
				</div>
				<div class="col-sm-12">
					<label class="control-label">المستوى التعليمي</label><span class="text-danger" style="font-size:20px;"> *</span>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="edLevel" id="edLevel1" value="ابتدائي" >
							ابتدائي
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="edLevel" id="edLevel2" value="إعدادي" >
							إعدادي
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="edLevel" id="edLevel3" value="ثانوي" >
							ثانوي
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="edLevel" id="edLevel4" value="جامعي" >
							جامعي
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="edLevel" id="edLevel5" value="غير ذلك" >
							غير ذلك
						</label>
					 </div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">التخصص العلمي</label>
					</div>
					<div class="col-sm-4"> 
						<input type="text" class="form-control" name="major" id="major" >
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">دورات تدريبية أخرى</label>
					</div>
					<div class="col-sm-7"> 
						<textarea class="form-control" name="TC" id="TC" rows="4"></textarea>
					</div>
				</div>
				<div class="col-sm-12">
					<h2>الحالة الوظيفية</h2>
				</div>
				<div class="col-sm-12">
					<label class="control-label">الحالة الوظيفة</label><span class="text-danger" style="font-size:20px;"> *</span>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="emState" id="emState1" value="عامل" >
							عامل
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="emState" id="emState2" value="عاطل" >
							عاطل
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="emState" id="emState3" value="متقاعد" >
							متقاعد
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="emState" id="emState4" value="طالب" >
							طالب
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="emState" id="emState5" value="" >
							أخرى
						</label>
					 </div>
				</div>
				<div class="form-group">
					<div class="col-sm-4"> 
						<input type="" class="form-control" onkeyup="if(document.getElementById('edLevel5').checked)document.getElementById('edLevel5').value=this.value" >
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">في حالة العمل: اذكر جهة العمل</label>
					</div>
					<div class="col-sm-4"> 
						<input type="text" class="form-control" name="employer" id="employer" >
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">في حالة العمل: اذكر المسمى الوظيفي- نوع الوظيفة</label>
					</div>
					<div class="col-sm-4"> 
						<input type="" class="form-control" name="jobName" id="jobName" >
					</div>
				</div>
				<div class="col-sm-12">
					<h2>الوضع الإجتماعي </h2>
				</div>
				<div class="col-sm-12">
					<label class="control-label">الحالة الإجتماعية:</label><span class="text-danger" style="font-size:20px;"> *</span>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="maState" id="maState1" value="عازب" >
							عازب
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="maState" id="maState2" value="متزوج" >
							متزوج
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="maState" id="maState3" value="مطلق" >
							مطلق
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="maState" id="maState4" value="أرمل" >
							أرمل
						</label>
					 </div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">عدد الأولاد: ذكور وإناث- إن وجد</label>
					</div>
					<div class="col-sm-4"> 
						<input type="number" class="form-control" name="kidNum" id="kidNum" >
					</div>
				</div>
				<div class="col-sm-12">
					<h2>معلومات عامة: <small>(للاستفادة منها في جميع أنشطة المأتم)</small></h2>
				</div>
				<div class="col-sm-12">
					<label class="control-label">هل سبق لك العمل في أي من المؤسسات الأخرى</label><span class="text-danger" style="font-size:20px;"> *</span>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="involved" id="involved1" value="نعم" >
							نعم
						</label>
					 </div>
				</div>
				<div class="col-sm-12">
					<div class="radio">
						<label>
							<input type="radio" name="involved" id="involved2" value="لا" >
							لا
						</label>
					 </div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">في حالة الإجابة (نعم): اذكر المؤسسات التي عملت بها</label>
					</div>
					<div class="col-sm-7"> 
						<textarea class="form-control" name="involvedName" id="involvedName" rows="4"></textarea>
					</div>
				</div>
				<div class="col-sm-12">
					<label class="control-label">اذكر المهارات الشخصية التي تتمتع بها</label><span class="text-danger" style="font-size:20px;"> *</span>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby1" value="شئون دينية" >
							شئون دينية
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby2" value="مالية وحسابات" >
							مالية وحسابات
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby3" value="خدمات وصيانة" >
							خدمات وصيانة
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby4" value="شئون ثقافية" >
							شئون ثقافية
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby5" value="شئون مكتبية" >
							شئون مكتبية
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby6" value="سكرتارية" >
							سكرتارية
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby7" value="علاقات عامة" >
							علاقات عامة
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby8" value="إعلام" >
							إعلام
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby9" value="طباخ" >
							طباخ
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby10" value="مشاريع واستثمار" >
							مشاريع واستثمار
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby11" value="إدارة فرق عمل" >
							إدارة فرق عمل
						</label>
					</div>
				</div>
				<div class="col-sm-1">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="hobby[]" id="hobby12" value="" >
							أخرى:
						</label>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-4"> 
						<input type="" class="form-control" onkeyup="document.getElementById('hobby12').value=this.value"  >
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">اذكر الهوايات والاهتمامات التي تعنيك</label>
					</div>
					<div class="col-sm-7"> 
						<textarea class="form-control" name="otherHobby" id="otherHobby" rows="4"></textarea>
					</div>
				</div>
				<div class="col-sm-12">
					<h2>معلومات الأبناء</h2>
				</div>
				<div class="form-group">
					<div class="col-sm-12">
						<label class="control-label">معلومات الأبناء الذكور</label>
						<span id="helpBlock" class="help-block">الصيغة المطلوبة: إسم الولد - الرقم الشخصي ، في حالة أكثر من ولد:معلومات كل ولد توضع في سطر خاص جديد</span>
					</div>
					<div class="col-sm-7"> 
						<textarea style="margin-bottom: 15px;" class="form-control" name="boys" id="boys" rows="4"></textarea>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-4 col-sm-offset-4">
							<button class="btn btn-success btn-block btn-lg" type="submit" name="add">إرسال</button>
							<button class="btn btn-danger btn-block btn-lg" type="reset" onclick="window.location=window.location" >إلغاء</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<?php
}

function checkSession($min)
{
	if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $min*60)) {
		// last request was more than 30 minutes ago
		session_unset();     // unset $_SESSION variable for the run-time 
		session_destroy();   // destroy session data in storage
		header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
		die;
	}
	$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
}

?>