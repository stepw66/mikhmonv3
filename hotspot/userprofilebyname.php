<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
session_start();
// hide all error
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {


  if (substr($userprofile, 0, 1) == "*") {
    $userprofile = $userprofile;
  } elseif (substr($userprofile, 0, 1) != "") {
    $getprofile = $API->comm("/ip/hotspot/user/profile/print", array(
      "?name" => "$userprofile",
    ));
    $userprofile = $getprofile[0]['.id'];
    if ($userprofile == "") {
      echo "<b>User Profile not found</b>";
    }
  }

  $getprofile = $API->comm("/ip/hotspot/user/profile/print", array(
    "?.id" => "$userprofile"
  ));
  $profiledetalis = $getprofile[0];
  $pid = $profiledetalis['.id'];
  $pname = $profiledetalis['name'];
  $psharedu = $profiledetalis['shared-users'];
  $pratelimit = $profiledetalis['rate-limit'];
  $ponlogin = $profiledetalis['on-login'];
  $sparent = $profiledetalis['parent-queue'];

  $getexpmode = explode(",", $ponlogin)[1];

  if ($getexpmode == "rem") {
    $getexpmodet = "Remove";
  } elseif ($getexpmode == "ntf") {
    $getexpmodet = "Notice";
  } elseif ($getexpmode == "remc") {
    $getexpmodet = "Remove & Record";
  } elseif ($getexpmode == "ntfc") {
    $getexpmodet = "Notice & Record";
  } else {
    $getexpmode = "0";
    $getexpmodet = "None";
  }

  $getprice = explode(",", $ponlogin)[2];
  if ($getprice == "0") {
    $getprice = "";
  } else {
    $getprice = $getprice;
  }

  $getvalid = explode(",", $ponlogin)[3];

  $getgracep = explode(",", $ponlogin)[4];

  $getlocku = explode(",", $ponlogin)[6];
  if ($getlocku == "") {
    $getprice = "Disable";
  } else {
    $getlocku = $getlocku;
  }

  $getallqueue = $API->comm("/queue/simple/print", array(
    "?dynamic" => "false",
  ));

  if (isset($_POST['name'])) {
    $name = ($_POST['name']);
    $sharedusers = ($_POST['sharedusers']);
    $ratelimit = ($_POST['ratelimit']);
    $expmode = ($_POST['expmode']);
    $validity = ($_POST['validity']);
    $graceperiod = ($_POST['graceperiod']);
    $getprice = ($_POST['price']);
    if ($getprice == "") {
      $price = "0";
    } else {
      $price = $getprice;
    }
    $getlock = ($_POST['lockunlock']);
    if ($getlock == Enable) {
      $lock = ';[:local mac $"mac-address"; /ip hotspot user set mac-address=$mac [find where name=$user]]';
    } else {
      $lock = "";
    }
    $parent = ($_POST['parent']);

    $onlogin1 = ':put (",rem,' . $price . ',' . $validity . ',' . $graceperiod . ',,' . $getlock . ',"); {:local date [/system clock get date ];:local time [/system clock get time ];:local uptime (' . $validity . ');[/system scheduler add disabled=no interval=$uptime name=$user on-event="[/ip hotspot active remove [find where user=$user]];[/ip hotspot user set limit-uptime=1s [find where name=$user]];[/sys sch re [find where name=$user]];[/sys script run [find where name=$user]];[/sys script re [find where name=$user]]" start-date=$date start-time=$time];[/system script add name=$user source=":local date [/system clock get date ];:local time [/system clock get time ];:local uptime (' . $graceperiod . ');[/system scheduler add disabled=no interval=\$uptime name=$user on-event= \"[/ip hotspot user remove [find where name=$user]];[/ip hotspot active remove [find where user=$user]];[/sys sch re [find where name=$user]]\"]"]';
    $onlogin2 = ':put (",ntf,' . $price . ',' . $validity . ',,,' . $getlock . ',"); {:local date [/system clock get date ];:local time [/system clock get time ];:local uptime (' . $validity . ');[/system scheduler add disabled=no interval=$uptime name=$user on-event= "[/ip hotspot user set limit-uptime=1s [find where name=$user]];[/ip hotspot active remove [find where user=$user]];[/sys sch re [find where name=$user]]" start-date=$date start-time=$time]';
    $onlogin3 = ':put (",remc,' . $price . ',' . $validity . ',' . $graceperiod . ',,' . $getlock . ',"); {:local price ("' . $price . '");:local date [/system clock get date ];:local time [/system clock get time ];:local uptime (' . $validity . ');[/system scheduler add disabled=no interval=$uptime name=$user on-event="[/ip hotspot active remove [find where user=$user]];[/ip hotspot user set limit-uptime=1s [find where name=$user]];[/sys sch re [find where name=$user]];[/sys script run [find where name=$user]];[/sys script re [find where name=$user]]" start-date=$date start-time=$time];[/system script add name=$user source=":local date [/system clock get date ];:local time [/system clock get time ];:local uptime (' . $graceperiod . ');[/system scheduler add disabled=no interval=\$uptime name=$user comment=$date-$time on-event= \"[/ip hotspot user remove [find where name=$user]];[/ip hotspot active remove [find where user=$user]];[/sys sch re [find where name=$user]]\"]"];:local bln [:pick $date 0 3]; :local thn [:pick $date 7 11];[:local mac $"mac-address"; :local profile [:put  [/ip hotspot user get $user profile]]; :local comment [:put [/ip hotspot user get $user comment]]; /system script add name="$date-|-$time-|-$user-|-$price-|-$address-|-$mac-|-' . $validity . '-|-$profile-|-$comment" owner="$bln$thn" source=$date comment=mikhmon]';
    $onlogin4 = ':put (",ntfc,' . $price . ',' . $validity . ',,,' . $getlock . ',"); {:local price ("' . $price . '");:local date [/system clock get date ];:local time [/system clock get time ];:local uptime (' . $validity . ');[/system scheduler add disabled=no interval=$uptime name=$user on-event= "[/ip hotspot user set limit-uptime=1s [find where name=$user]];[/ip hotspot active remove [find where user=$user]];[/sys sch re [find where name=$user]]" start-date=$date start-time=$time];:local bln [:pick $date 0 3]; :local thn [:pick $date 7 11];[:local mac $"mac-address"; :local profile [:put  [/ip hotspot user get $user profile]]; :local comment [:put [/ip hotspot user get $user comment]]; /system script add name="$date-|-$time-|-$user-|-$price-|-$address-|-$mac-|-' . $validity . '-|-$profile-|-$comment" owner="$bln$thn" source=$date comment=mikhmon]';

    if ($expmode == "rem") {
      $onlogin = $onlogin1 . $lock . "}}";
    } elseif ($expmode == "ntf") {
      $onlogin = $onlogin2 . $lock . "}}";
    } elseif ($expmode == "remc") {
      $onlogin = $onlogin3 . $lock . "}}";
    } elseif ($expmode == "ntfc") {
      $onlogin = $onlogin4 . $lock . "}}";
    } elseif ($expmode == "0" && $price != "") {
      $onlogin = ':put (",,' . $price . ',,,noexp,' . $getlock . ',")' . $lock;
    } else {
      $onlogin = "";
    }

    $API->comm("/ip/hotspot/user/profile/set", array(
			  		  /*"add-mac-cookie" => "yes",*/
      ".id" => "$pid",
      "name" => "$name",
      "rate-limit" => "$ratelimit",
      "shared-users" => "$sharedusers",
      "status-autorefresh" => "1m",
      "transparent-proxy" => "yes",
      "on-login" => "$onlogin",
      "parent-queue" => "$parent",
    ));
    echo "<script>window.location='./?user-profile=" . $pid . "&session=" . $session . "'</script>";
  }
}
?>
<div class="row">
<div class="col-8">
<div class="card">
<div class="card-header">
    <h3><i class="fa fa-edit"></i> <?= $_edit." ".$_user_profile ?></h3>
</div>
<div class="card-body">
<form autocomplete="off" method="post" action="">
  <div>
    <a class="btn bg-warning" href="./?hotspot=user-profiles&session=<?= $session; ?>"> <i class="fa fa-close"></i> <?= $_close?></a>
    <button type="submit" name="save" class="btn bg-primary" ><i class="fa fa-save"></i> <?= $_save ?></button>
    <a class="btn bg-danger" href="./?remove-user-profile=<?= $pid; ?>&session=<?= $session; ?>"><i class="fa fa-minus-square"></i> <?= $_remove ?></a>
  </div>
<table class="table">
  <tr>
    <td><?= $_name ?></td><td><input class="form-control" type="text" onchange="remSpace();" autocomplete="off" name="name" value="<?= $pname; ?>" required="1" autofocus></td>
  </tr>
  <tr>
    <td>Shared Users</td><td><input class="form-control" type="text" size="4" autocomplete="off" name="sharedusers" value="<?= $psharedu; ?>" required="1"></td>
  </tr>
  <tr>
    <td>Rate limit [up/down]</td><td><input class="form-control" type="text" name="ratelimit" autocomplete="off" value="<?= $pratelimit; ?>" placeholder="Example : 512k/1M" ></td>
  </tr>
  <tr>
    <td><?= $_expired_mode ?></td><td>
      <select class="form-control" onchange="RequiredV();" id="expmode" name="expmode" required="1">
        <option value="<?= $getexpmode; ?>"><?= $getexpmodet; ?></option>
        <option value="0">None</option>
        <option value="rem">Remove</option>
        <option value="ntf">Notice</option>
        <option value="remc">Remove & Record</option>
        <option value="ntfc">Notice & Record</option>
      </select>
    </td>
  </tr>
  <tr id="validity" style="display:none;">
    <td><?= $_validity ?></td><td><input class="form-control" type="text" id="validi" size="4" autocomplete="off" name="validity" value="<?= $getvalid; ?>" required="1"></td>
  </tr>
  <tr id="graceperiod" style="display:none;">
    <td><?= $_grace_period ?></td><td><input class="form-control" type="text" id="gracepi" size="4" autocomplete="off" name="graceperiod" value="<?= $getgracep; ?>" required="1"></td>
  </tr>
  <tr>
    <td><?= $_price." ". $currency; ?></td><td><input class="form-control" type="text" min="0" name="price" value="<?= $getprice; ?>" ></td>
  </tr>
  <tr>
    <td><?= $_lock_user ?></td><td>
      <select class="form-control" id="lockunlock" name="lockunlock" required="1">
        <option value="<?= $getlocku; ?>"><?= $getlocku; ?></option>
        <option value="Enable">Enable</option>
        <option value="Disable">Disable</option>
      </select>
    </td>
  </tr>
  <tr>
    <td class="align-middle">Parent Queue</td>
    <td>
    <select class="form-control " name="parent">
      <option><?= $sparent; ?></option>
      <option>none</option>
        <?php $TotalReg = count($getallqueue);
        for ($i = 0; $i < $TotalReg; $i++) {

          echo "<option>" . $getallqueue[$i]['name'] . "</option>";
        }
        ?>
    </select>
    </td>
  </tr>
</table>
</form>
</div>
</div>
</div>
<div class="col-4">
  <div class="card">
    <div class="card-header">
      <h3><i class="fa fa-book"></i> <?= $_readme ?></h3>
    </div>
    <div class="card-body">
<table class="table">
    <tr>
    <td colspan="2">
      <p style="padding:0px 5px;">
        <?= $_details_user_profile ?>
      </p>
      <p style="padding:0px 5px;">
        <?= $_format_validity_grace_period ?>
      </p>
    </td>
  </tr>
</table>
</div>
</div>
</div>
</div>
<script type="text/javascript">
function remSpace() {
  var upName = document.getElementsByName("name")[0];
  var newUpName = upName.value.replace(/\s/g, "-");
  alert("<?php if ($currency == in_array($currency, $cekindo['indo'])) {
            echo "Nama Profile tidak boleh berisi spasi";
          } else {
            echo "Profile name can't containing white space!";
          } ?>");
  upName.value = newUpName;
  upName.focus();
}
</script>