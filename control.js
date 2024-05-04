function isUser(e) {
    return isCPRFound = !1, e.length < 9 || e.length > 9 ? (byId("search").disabled = !0, void(byId("search").className = "btn btn-default")) : (e = parseArabic(e), byId("CPR").value = e, void(9 != e.length || isNaN(e) || (e = parseArabic(e), findUserajax(e, "u"))))
}

function findUserajax(e,t){
	$.ajax({
		url: "request.php?CPR=" + e + "&t=" + t + "&random=" + Math.random(),
		success: function(result){
			if(result == "exist"){
				byId("search").className = "btn btn-success";
				byId("search").disabled = false;
			} else {
                byId("search").className = "btn btn-danger";
            }
    }});
}

function showQueueInfo(e, d) {
    d = "undefined" != typeof d ? d : "استعراض طلب تعديل بيانات العضوية", CPRInfoFromTable(e, "q", ""), byId("previewModalLabel").innerHTML = d, $("#previewModal").modal("show")
}

function showUserInfo(e, d) {
    d = "undefined" != typeof d ? d : "إستمارة بيانات العضوية", CPRInfoFromTable(e, "u"), byId("previewModalLabel").innerHTML = d, $("#previewModal").modal("show")
}

function confDelete(e) {
    if (e == 'true') return true;
    else if (e.indexOf("حذف البيانات") > -1) {
        return confirm("هل أنت متأكد من " + e + " ؟") ? confirm("متأكد من خيار " + e + " ؟!") : !1
    } else return confirm("هل أنت متأكد من " + e + " ؟")
}

function addchildRow() {
    byId("childNum").value = parseInt(byId("childNum").value) + 1;
    var e = '	<tr id="childRow' + byId("childNum").value + '">		<td>			<div style="padding-right: 0px; padding-left: 5px;">				<div id="childDiv' + byId("childNum").value + '" class="form-group">					<input class="form-control" type="text" maxlength="9" id="childCPR' + byId("childNum").value + '" name="childCPR[]" placeholder="CPR الإبن .." onkeyup="findFather(this.value,' + byId("childNum").value + ')">					<span id="icon' + byId("childNum").value + '" class="glyphicon glyphicon-ok form-control-feedback" aria-hidden="true"></span>					<input type="hidden" id="validChild' + byId("childNum").value + '" value="false">				</div>			</div>		</td>		<td id="childName' + byId("childNum").value + '">		--		</td>		<td>			<button type="button" class="btn btn-danger" onclick="byId(\'childRow' + byId("childNum").value + '\').remove();" ><span class="glyphicon glyphicon-minus" aria-hidden="true"></span> حذف الإبن</button>		</td>	</tr>';
    byId("tableBody").insertAdjacentHTML("beforeend", e)
}

function findFather(e, d) {
    return isCPRFound = !1, isChild = !0, e.length < 9 || e.length > 9 ? (byId("childDiv" + d).className = "form-group", byId("childName" + d).innerHTML = "--", void(byId("validChild" + d).value = "false")) : (e = parseArabic(e), byId("childCPR" + d).value = e, void(9 != e.length || isNaN(e) || e == byId("fatherCPR").value ? (byId("childDiv" + d).className = "form-group has-error has-feedback", byId("icon" + d).className = "glyphicon glyphicon-remove form-control-feedback", byId("childName" + d).innerHTML = "الرقم الشخصي غير صحيح ..", byId("validChild" + d).value = "false") : (findCPR(e, "u"), childCheck(e), isCPRFound && !isChild ? (byId("childDiv" + d).className = "form-group has-success has-feedback", byId("icon" + d).className = "glyphicon glyphicon-ok form-control-feedback", childID = d, CPRInfoFromTable(e, "u"), byId("validChild" + d).value = "true") : (byId("childDiv" + d).className = "form-group has-error has-feedback", byId("icon" + d).className = "glyphicon glyphicon-remove form-control-feedback", byId("childName" + d).innerHTML = "الرقم الشخصي غير صحيح أو يمتلك والد بالفعل ..", byId("validChild" + d).value = "false"))))
}

function checkChildForm() {
    var e = parseInt(byId("childNum").value) + 1;
    for (i = 0; i < e; i++)
        if (null != byId("validChild" + i)) {
            if (0 == byId("childCPR" + i).value.length) {
                byId("childRow" + i).remove();
                continue
            }
            if (byId("childCPR" + i).value.length < 9) return byId("childDiv" + i).className = "form-group has-error has-feedback", byId("icon" + i).className = "glyphicon glyphicon-remove form-control-feedback", byId("childName" + i).innerHTML = "الرقم الشخصي غير صحيح ..", byId("validChild" + i).value = "false", !1;
            if ("false" == byId("validChild" + i).value) return !1;
            for (j = i + 1; j < e; j++)
                if (byId("childCPR" + i).value == byId("childCPR" + j).value) return byId("childDiv" + j).className = "form-group has-error has-feedback", byId("icon" + j).className = "glyphicon glyphicon-remove form-control-feedback", byId("childName" + j).innerHTML = "الرقم الشخصي مكرر ..", byId("validChild" + j).value = "false", !1
        }
    return confirm("هل أنت متأكد من حفظ البيانات ؟")
}
var byId = function(e) {
        return document.getElementById(e)
    },
    childID;