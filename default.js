function checkCPR(e) {
    if (e.length < 9 || e.length > 9) {
        $("#check").prop("disabled", true);
        $("#div").attr('class', 'form-group has-error');
        $("#span").attr('class', '');
    } else {
        e = parseArabic(e);
        byId("CPR").value = e;
        if (9 === e.length && !isNaN(e)) {
            e = parseArabic(e);
            document.getElementById("div").className = "form-group";
            makeRequest(e);
        }
    }
}

function makeRequest(e, t) {
    if(!t) {
        t =''
    }
    $.ajax({
        url: "request.php?CPR=" + e + "&t=" + t + "&random=" + Math.random(),
        async: false,
		success: function(result){
            if (result.trim() === "exist"){
                $("#check").prop("disabled", false);
                $("#div").attr('class', 'form-group has-success has-feedback');
                $("#span").attr('class', 'glyphicon glyphicon-ok form-control-feedback');
            } else {
                $("#check").prop("disabled", true);
                $("#div").attr('class', 'form-group has-error has-feedback');
                $("#span").attr('class', 'glyphicon glyphicon-remove form-control-feedback');
            }
        },
        error: function (){
            $("#check").prop("disabled", true);
            $("#div").attr('class', 'form-group has-error has-feedback');
            $("#span").attr('class', 'glyphicon glyphicon-remove form-control-feedback');
        }
    });
}

function findCPR(e, t) {
    $.ajax({
        url: "request.php?CPR=" + e + "&t=" + t + "&random=" + Math.random(),
        async: false,
		success: function(result){
            isCPRFound = (result.trim() === "exist");
        },
        error: function (){
            isCPRFound = false;
        }
    });
}

function childCheck(e) {
    $.ajax({
        url: "request.php?child=" + e + "&random=" + Math.random(),
        async: false,
		success: function(result){
            isChild = (result.trim().substring(0, 5) === "exist");
        },
        error: function (){
            isChild = false;
        }
    });
}

function checkForm() {
    isNewCPR = !0;
    var e = [];
    for (byId("error").innerHTML = "", (byId("name").value.length = 0 || "" == byId("name").value.trim() || !byId("name").value.match(/^[\u0620-\u063A\u0641-\u064A ]+$/)) && e.push("الاسم الثلاثي"), byId("CPR").value = parseArabic(byId("CPR").value), 9 != byId("CPR").value.length || isNaN(byId("CPR").value) ? e.push("الرقم الشخصي") : (findCPR(byId("CPR").value), isCPRFound && byId("oldCPR") === null && !byId("CPR").disabled && e.push("الرقم الشخصي موجود مسبقاً")), (byId("dob").value.length = 0 || "" == byId("dob").value.trim() || !byId("dob").value.match(/[\d]{4}\-[\d]{2}\-[\d]{2}/g)) && e.push("تاريخ الميلاد"), byId("phone").value = parseArabic(byId("phone").value), (byId("phone").value.length < 8 || byId("phone").value.match(/[a-zA-Z]/g) || byId("phone").value.match(/[\u0600-\u06FF]/g)) && e.push("رقم الهاتف"), e.push("المستوى التعليمي"), i = 0; 5 > i; i++)
        if (byId("edLevel" + (i + 1)).checked) {
            e.pop();
            break
        }
    for (e.push("الحالة الوظيفية"), i = 0; 5 > i; i++)
        if (byId("emState" + (i + 1)).checked) {
            e.pop();
            break
        }
    for (e.push("الحالة الإجتماعية"), i = 0; 4 > i; i++)
        if (byId("maState" + (i + 1)).checked) {
            e.pop();
            break
        }
    for (byId("kidNum").value = parseArabic(byId("kidNum").value), "" == byId("kidNum").value.trim() ? byId("kidNum").value = "0" : isNaN(byId("kidNum").value) && e.push("عدد الأبناء"), e.push("العمل في المؤسسات السابقة"), i = 0; 2 > i; i++)
        if (byId("involved" + (i + 1)).checked) {
            e.pop();
            break
        }
    for (byId("involved1").checked && "" == byId("involvedName").value.trim() && e.push("اسماء المؤسسات"), e.push("المهارات الشخصية"), i = 0; 12 > i; i++)
        if (byId("hobby" + (i + 1)).checked) {
            e.pop();
            break
        }
    if (e.length > 0) {
        for (i = 0; i < e.length; i++) byId("error").innerHTML = byId("error").innerHTML + errorBox(e[i], i);
        return $("#myModal").modal("show"), !1
    }
    return !0
}

function parseArabic(e) {
    return e.replace(/[٠١٢٣٤٥٦٧٨٩]/g, function(e) {
        return e.charCodeAt(0) - 1632
    })
}

function printModal(e, t, n, d, i) {
    CPRInfoFromTable(e, n);
    var s = byId(t).innerHTML,
        o = document.body.innerHTML;
    document.body.innerHTML = "<html><head><title></title></head><body>" + s + "</body>", $("#hideModal").remove(), byId("previewModalHeader").innerHTML = "<table style='width=100%'><tr><td style='width:91%'><h4 class='modal-title text-danger' id='previewModalLabel'>" + d + "</h4></td><td><img src='M-Logo.png' width='45px' style='margin-right: 15px;'></td></tr></table>", byId("previewModalFooter").innerHTML = "<h5>" + i + "</h5>", window.print(), document.body.innerHTML = o, document.body.removeAttribute("class"), document.body.removeAttribute("style")
}

function CPRInfoFromTable(e, t) {
    $.ajax({
        url: "request.php?CPR=" + e + "&table=" + t + "&random=" + Math.random(),
        async: false,
		success: function(result){
            var e = JSON.parse(decodeURIComponent(result));
            if (1 == e.success) {
                if (delete e.success, null != byId("info")) {
                    if (e['active'] == '0') var c = 'style="background-color:#F55; color:#fff"';
                    else var c = '';
                    delete e['active'];
                    var t = '<table ' + c + 'class="table"><tbody>';
                    for (var n in e) t += '<tr><td style="width:25%">' + n + "</td><td>" + e[n] + "</td></tr>";
                    t += "</tbody></table>", byId("info").innerHTML = t
                }
                null != byId("childName" + childID) && (byId("childName" + childID).innerHTML = e["الاسم"])
            } else if(byId("info")) {
                byId("info").innerHTML = e.message;
            }
        },
        error: function (){
            if(byId("check")) {
                byId("check").disabled = false;
            }
        }
    });

}! function() {
    null != document.getElementById("check") && (document.getElementById("check").disabled = !0)
}();

var isCPRFound, 
isChild;

function byId(e) {
    return document.getElementById(e)
};

function errorBox(e) {
    return "<h4>" + (i + 1) + ") " + e + "</h4>"
};


// init date picker
if ($("#dob").length){
    $("#dob").datepicker({
        yearRange: '-150:+0',
        monthNamesShort: ["ياناير", "فبراير", "مارس", "أبريل", "مايو", "يونيو", "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر"],
        isRTL: true,
        showMonthAfterYear: true,
        dateFormat: 'yy-mm-dd',
        changeYear: true,
        changeMonth: true
    });
}