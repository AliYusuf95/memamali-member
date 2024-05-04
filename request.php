<?php

require_once ('config.php');

if (isset($_GET['CPR']) && isset($_GET['t']))
{
	if ($_GET['t'] == ''){
	try{
	$getCPR= $db->prepare("select CPR from user where CPR =? AND active=1 union select CPR from queue where CPR =?");
	$getCPR->execute(array($_GET['CPR'],$_GET['CPR']));
	if ($getCPR->rowCount() == 1) {
        //$getDOB = $db->prepare("SELECT dob FROM user WHERE CPR =?");
        //$getDOB->execute(array($_GET['CPR']));
        echo "exist "; //. $getDOB->fetch(PDO::FETCH_NUM)[0];
    }
	else
		echo "not exist";
	}
	catch(PDOException $ex) {
		//user friendly message
		echo "not exist";
	}
	}
	else if ($_GET['t'] == 'u'){
	try{
	$getCPR= $db->prepare("select CPR from user where CPR =?");
	$getCPR->execute(array($_GET['CPR']));
	if ($getCPR->rowCount() == 1)
		echo "exist";
	else
		echo "not exist";
	}
	catch(PDOException $ex) {
		//user friendly message
		echo "not exist";
	}
	}
	else if ($_GET['t'] == 'q'){
	try{
	$getCPR= $db->prepare("select CPR from user where CPR =?");
	$getCPR->execute(array($_GET['CPR']));
	if ($getCPR->rowCount() == 1)
		echo "exist";
	else
		echo "not exist";
	}
	catch(PDOException $ex) {
		//user friendly message
		echo "not exist";
	}
	}
}
else if (isset($_GET['CPR']) && isset($_GET['table']))
{
	if ($_GET['table'] == 'q')
	{
	try{
	$user= $db->prepare("select `name`, `CPR`, `dob` , `phone`, `edLevel`, `major`, `TC`, `emState`, `employer`, `jobName`, `maState`, `kidNum`, `involved`, `involvedName`, `hobby`, `otherHobby`, `boys` from queue where CPR =?");
	$user->execute(array($_GET['CPR']));
	$user = $user->fetch(PDO::FETCH_NUM);
	$col_name = array('الاسم','الرقم الشخصي','تاريخ الميلاد', 'الهاتف','المستوى التعليمي','التخصص','دورات تدريبية أخرى','الحالة الوظيفية','مكان الوظيفة','المسمى الوظيفي','الحالة الإجتماعية','عدد الأولاد','مرتبط بمؤسسة','اسماء المؤسسات','مهارات','هوايات','الأولاد الذكور');
	$json = array_combine($col_name,$user);
	$json['success']=1;
	array_walk($json,function(&$item){$item=strval($item);});
	echo json_encode($json, JSON_UNESCAPED_UNICODE);
	}catch(PDOException $ex) {
		//user friendly message
		$json['success']=0;
		$json['message']="حدث خطأ في تحميل البيانات .. يرجى المحاولة لاحقاً";
		echo json_encode($json, JSON_UNESCAPED_UNICODE);
	}
	}
	else if ($_GET['table'] == 'u')
	{
	try{
	$user= $db->prepare("select `name`, `CPR`, `dob`, `phone`, `edLevel`, `major`, `TC`, `emState`, `employer`, `jobName`, `maState`, `kidNum`, `involved`, `involvedName`, `hobby`, `otherHobby`, `boys`, `active` from user where CPR =?");
	$user->execute(array($_GET['CPR']));
	$user = $user->fetch(PDO::FETCH_NUM);
	$col_name = array('الاسم','الرقم الشخصي','تاريخ الميلاد','الهاتف','المستوى التعليمي','التخصص','دورات تدريبية أخرى','الحالة الوظيفية','مكان الوظيفة','المسمى الوظيفي','الحالة الإجتماعية','عدد الأولاد','مرتبط بمؤسسة','اسماء المؤسسات','مهارات','هوايات','الأولاد الذكور','active');
	$json = array_combine($col_name,$user);
	$json['success']=1;
	array_walk($json,function(&$item){$item=strval($item);});
	echo json_encode($json, JSON_UNESCAPED_UNICODE);
	}catch(PDOException $ex) {
		//user friendly message
		$json['success']=0;
		$json['message']="حدث خطأ في تحميل البيانات .. يرجى المحاولة لاحقاً";
		echo json_encode($json, JSON_UNESCAPED_UNICODE);
	}
	}
}
if (isset($_GET['child']))
{
	try{
	$getCPR= $db->prepare("select parent from father where child =?");
	$getCPR->execute(array($_GET['child']));
	if ($getCPR->rowCount() == 1)
		echo "exist";
	else
		echo "not exist";
	}
	catch(PDOException $ex) {
		//user friendly message
		echo "not exist";
	}
}