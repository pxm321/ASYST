<?php
/*****************************************************************************

	    COPYRIGHT (c) 2010, 2017 by Reprise Software, Inc.
	This software has been provided pursuant to a License Agreement
	containing restrictions on its use.  This software contains
	valuable trade secrets and proprietary information of 
	Reprise Software Inc and is protected by law.  It may not be 
	copied or distributed in any form or medium, disclosed to third 
	parties, reverse engineered or used in any manner not provided 
	for in said License Agreement except with the prior written 
	authorization from Reprise Software Inc.

 	$Id: rlc_report.php,v 1.22 2017/04/13 15:35:12 matt Exp $

 *****************************************************************************/

include "rlc.php";
include "rlc_prompts.php";
include "rlc_mysql.php";
include 'login_session.php';

/*
 *	RLM Activation Pro reporting code
 */

/*
 *	To edit the columns we process, edit the variable $cols below
 */

define("RLC_REPORT_HTML",     0); /* Report to screen */


/*
 *	Low-level output functions that switch between
 *	the screen and a report file.
 */

function report_title($header, $output)
{
	if ($output == RLC_REPORT_HTML) rlc_web_title($header, 0);
	else 
	{ 
		echo $header; 
		echo "<br>\n"; 
	}
}

function report_start($output, $filename, &$file)
{
	if ($output == RLC_REPORT_HTML) rlc_web_tableheader();
	else 
	{
		$file = fopen($filename, "w");
		if ($file == FALSE)
		{
			echo "<br>ERROR opening temporary file ".$filename."<br>";
		}
		else
		{
			$file2 = fopen($filename.".php", "w");
			if ($file2 == FALSE)
			{
			  echo "<br>ERROR opening temporary file ".$filename.".php<br>";
			}
			else
			{
				fwrite($file2, "<?php\n");
				fwrite($file2, "header('Content-type: text/plain');\n");
				fwrite($file2, "header('Content-disposition: attachment; filename=report.txt');\n");
				fwrite($file2, "readfile('$filename');\n");
				fwrite($file2, "?>\n");
				fclose($file2);
				chmod($filename.".php", 0600);
			}
		}
	}
}

function report_date($output, $file)
{
	$header = "Report generated by RLM Activation Pro v".RLC_ACTPRO_VERSION.
					".".RLC_ACTPRO_REV."BL".
					RLC_ACTPRO_BUILD."-p".RLC_ACTPRO_PATCH.
					" (".RLC_ACTPRO_DATE.")";
	$errlevel = rlc_turn_off_warnings();
	$date = "Generated on ".date("l j M Y h:i A \G\M\T"); 
	error_reporting($errlevel);

	if ($output == RLC_REPORT_HTML)
	{
		echo "<br>".$header."<br>";
		echo "<br>".$date."<br><br>";
	}
	else
	{
		fwrite($file, $header."\n");
		fwrite($file, $date."\n\n");
	}

}

function report_end($output, $file)
{
	if ($output == RLC_REPORT_HTML) rlc_web_table_end();
	else 
	{
		fwrite($file, "\n");
	}
}

function report_row($output, $file)
{
	if ($output == RLC_REPORT_HTML) rlc_web_row_start();
}
function report_row_end($output, $file)
{
	if ($output == RLC_REPORT_HTML) rlc_web_row_end();
	else fwrite($file, "\n");
}
function report_header_element($output, $file, $element, $elemname, $sort, $sc,
									$sd)
{
	if ($output == RLC_REPORT_HTML)
	{
		rlc_web_header_elem($element, $sort, $elemname, $sc, $sd);
	}
	else 
	{
		$temp = sprintf("%s%c", $element, $output);
		fwrite($file, $temp);
	}
}
function report_element($output, $file, $element)
{
	if ($output == RLC_REPORT_HTML) rlc_web_row_elem($element);
	else 
	{
/*
 *		v12.3 - replace \n and \r with spaces
 */
		$new = str_replace("\n", " ", $element);
		$new2 = str_replace("\r", " ", $new);
		$temp = sprintf("%s%c", $new2, $output);
		fwrite($file, $temp);
	}
}
function report_element_num($output, $file, $element)
{
	if ($output == RLC_REPORT_HTML) rlc_web_row_elem_num($element);
	else 
	{
		$temp = sprintf("%d%c", $element, $output);
		fwrite($file, $temp);
	}
}

/*
 *	Output a time for a special field
 */

function print_time($rep_output, $file, $what)
{
	$errlevel = rlc_turn_off_warnings();
	if (($what == NULL) || ($what == 0)) $timestr = "-";
	else $timestr = date("Y-m-d H:i T", $what);	
        report_element($rep_output, $file, $timestr);
	error_reporting($errlevel);
}

/*
 *	do_report() - produce the report, given all the other paramters.
 */

function do_report($sql, $report, $header, $rep_output, $extra, $pxtra, $post)
{
	$select = get_select($post);
	$extra = $extra."&select=".$select;
	$pxtra = $pxtra."<input type=hidden name=select value='$select'>";

	rlc_web_get_pagination($r1, $rpp, $tr, $sc, $sd, "rlc-rpp-rpt");

/*
 *	Remove old report files
 */
	$files = glob("./tmp/*");
	foreach ($files as $file) unlink($file);
	
	report_title($header, $rep_output);
	if ($rep_output)
	{
		$inactive = " (inactive)";
		$filename = tempnam("./tmp/", "actpro_report_");
	}
	else
	{
		$inactive = " (<i>inactive</i>)";
		$filename = "";
	}

	$colselect = sprintf(" WHERE (report=\"%s\")", $report);
	$numcols = rlc_mysql_read_report_cols($sql, $reportcols, $colselect,
					$tablecolumn, $display, $is_int);
	if ($numcols == 0)
	{
		rlc_web_info("Report Definition Records Not Found");
		finish_page(0, 0, 0);
		rlc_mysql_close($sql);
		return;
	}

/*
 *	Get the product definitions for the activation keys report
 */
	$numproducts = rlc_mysql_read_products($sql, $products, $lictype, 
						$license, $version, 
						$version_type, $active, 
						$obsolete, "", "", 1);
	if ($numproducts < 0)
	{
	    	rlc_web_info("Cannot read product definitions<br>");
		finish_page(0, 0, 0);
		rlc_mysql_close($sql);
		return;
	}

	$query = "SELECT * from report_types where report = '".$report."'";
	$stat = rlc_mysql_get_table($sql, $res, $query);
	if ($stat == 0) $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
	mysqli_free_result($res);

	$table = $row['tablename'];
	$sort = rlc_mysql_sort($sc, $sd);
	_rlc_sql_fmt($select, $sel);
	$query = "select * from ".$table." ".$sel." ".$sort;
	$stat = rlc_mysql_get_table($sql, $results, $query);
	if ($stat != 0)
	{
		rlc_web_info("No Matching Records Found");
		finish_page(0, 0, 0);
		rlc_mysql_close($sql);
		return;
	}

	if ($tr == 0)
	{
		$tr = mysqli_num_rows($results);
	}
	if ($r1 > 1)
	{
		mysqli_data_seek($results, $r1-1);
	}
	$colsort = $extra."&r1=".$r1."&rpp=".$rpp."&tr=".$tr;
	
	report_start($rep_output, $filename, $file);
	report_date($rep_output, $file);
	report_row($rep_output, $file);
	for ($i = 0; $i < $numcols; $i++)
	{
		report_header_element($rep_output, $file, $display[$i], 
				      $tablecolumn[$i], $colsort, $sc, $sd);
	}
	report_row_end($rep_output, $file);
	$numdisp = 0;
	while ($row = mysqli_fetch_array($results, MYSQLI_ASSOC))
	{
	    report_row($rep_output, $file);
	    for ($i = 0; $i < $numcols; $i++)
	    {
		if (!strcmp($tablecolumn[$i], "exp"))
		{
		    if ($row['exp'] == "") $expdate = "";
		    else if (strpos($row['exp'], '-')) $expdate = $row['exp'];
		    else if ($row['exp'] == "0")  $expdate = "permanent";
		    else 			$expdate = $row['exp']. " days";
		    report_element($rep_output, $file, $expdate);
		}
		else if (!strcmp($tablecolumn[$i], "lictype"))
		{
		    $lt = license_type($row['lictype']);
		    report_element($rep_output, $file, $lt);
		}
		else if (!strcmp($tablecolumn[$i], "version"))
		{
			if ($row['version_type'] == 0)
				$vvv = $row['version'];
			else
				$vvv = "+ ".$row['version']." mo.";
			report_element($rep_output, $file, $vvv);
		}
		else if (!strcmp($tablecolumn[$i], "kver"))
		{
			if ($row['kver_type'] == 0)
				$vvv = $row['kver'];
			else
				$vvv = "+ ".$row['kver']." mo.";
			report_element($rep_output, $file, $vvv);
		}
        	else if (!strcmp($tablecolumn[$i], "@time")) 
		{ 
		    print_time($rep_output, $file, $row['time']);
                }
        	else if (!strcmp($tablecolumn[$i], "@revoke_time"))
                {
		    print_time($rep_output, $file, $row['revoke_time']);
                }
        	else if (!strcmp($tablecolumn[$i], "@last_fulfill"))
                {
		    print_time($rep_output, $file, $row['last_fulfill']);
                }
        	else if (!strcmp($tablecolumn[$i], "@last_check"))
                {
		    print_time($rep_output, $file, $row['last_check']);
                }
		else if (!strcmp($tablecolumn[$i], "@fulfill_count"))
		{
		    $k = rlc_mysql_get_fulfilled($sql, $row['akey']);
		    if ($k == 0) $num = 0; else $num = $k['num'];
		    report_element_num($rep_output, $file, $num);
		}
		else if (!strcmp($tablecolumn[$i], "@product"))
		{
		    $pid = $row['product_id'];
		    if (array_key_exists($pid, $products) && $products[$pid])
		    {
			if (!$active[$pid])
				$thisproduct = $products[$pid].$inactive;
			else
				$thisproduct = $products[$pid];
		    }
		    else
			$thisproduct = "???";

		    report_element($rep_output, $file, $thisproduct);
		}
		else if (!strcmp($table, "prod") &&
		         !strcmp($tablecolumn[$i], "name"))
		{
			$p = $row[$tablecolumn[$i]];
			if (!$row['active']) $p = $p.$inactive;
		        report_element($rep_output, $file, $p);
		}
		else if (!strcmp($tablecolumn[$i], "type"))
		{
		    $act_types = array("Normal", "ReActivate", "Refresh", 
								"Normal-Regen");
		    report_element_num($rep_output, $file, 
		    				$act_types[$row['type']]);
		}
		else
		{
		    if ($is_int[$i])
		        report_element_num($rep_output, $file, 
							$row[$tablecolumn[$i]]);
		    else
		        report_element($rep_output, $file, 
							$row[$tablecolumn[$i]]);
		}
	    }
	    report_row_end($rep_output, $file);
	    $numdisp++;
	    if (($rep_output == 0) && ($numdisp >= $rpp)) break;
	}
	report_end($rep_output, $file);
	mysqli_free_result($results);
/*
 *	If we generated an output file, create the download link here.
 */
	if ($rep_output)
	{
		echo "$numdisp Records output<br>";
/*
 *		Get to the last "tmp/" in the filename.
 */
 		$ds = DIRECTORY_SEPARATOR;
		$fn = $ds."tmp".$ds.$filename;
		while ($fn)
		{
			$last = $fn;
			$fn = strstr(substr($fn, 1), "tmp".$ds);
		}
		$fn = "\"$last.php\"";
		$fn[4] = '/';	/* Force to forward slash for browser */
		echo "<br><br><a href=$fn>Click here to download report.</a><br><br>";
		fclose($file);
	}
	else
	{
	    rlc_web_paginate("rlc_report.php", $extra, $pxtra, $rpp, $r1, 
					$r1 + $numdisp - 1, $tr, $sc, $sd, "");
	}

	rlc_mysql_close($sql);
}

/*
 *	Get the selection variables from the POST array
 */

function _rlc_remove_backslash($in, &$out)
{
	$out = " ";
	$inlen = strlen($in);
	$p = 0;  $q = 0;
	while ($p < $inlen)
	{
		if (($in[$p] == '\\') && ($in[$p+1] == '\''))
		{
			$p++;
		}
		else
			$out[$q++] = $in[$p++];
	}
}

function get_select($post)
{
/*
 *	The columns we process
 */

  $cols = array("akey", "company", "contact", "count", "date", "exp", "expdate", "issued", "lastdate", "license", "license_hostid", "misc", "name", "notes", "num", "product", "product_id", "reference_hostid", "rehosts", "remote_host", "revoked", "u1", "u2", "u3", "u4", "u5", "u6", "u7", "u8", "u9", "u10", "upgrade_version", "version", "white");

	$gotone = 0;
	$select = "";

	if (($select == "") && array_key_exists('select', $_GET))
	{
		 _rlc_remove_backslash($_GET['select'], $select);
		/***
		echo "select comes from _GET array is: ".$select."<br>";
		***/
	}
	else
	{
	  foreach ($cols as $col)
	  {
	    if (array_key_exists($col, $post) && ($post[$col] != ""))
	    {
/*
 *		Special case for "all products"
 */
 		if ($col == "product_id" && $post[$col] == "*all*")
			continue;

		$opspec = $col."_op";
		if (array_key_exists($opspec, $post)) 
		{
		    $op = $post[$opspec];
		    if ($op == "lt") $op = "<";
		    else if ($op == "gt") $op = ">";
		}
		else if ($col == "product_id") 
		{
		    $op = "=";
		}
		else
		{
		    $op = "REGEXP";
		}

		if ($post[$col] == "NULL") $value = "NULL";
		else			   $value = "<".$post[$col]."<";

		if ($gotone) $select = $select." AND $col $op $value";
		else $select = " WHERE $col $op $value";
		$gotone = 1;
	    }
	  }
	}
	return($select);
}


/*
 *	Get the selection data for a report
 *	This is the 2nd screen after the report type is picked.
 */

function get_selection_data($sql, $type)
{
/*
 *	lastdisplay fixes the case where a database upgrade is done
 *	multiple times so that the selection criteria appear multiple
 *	times in the database.
 *
 *	This also means that 2 different selection criteria can't have
 *	the *same* displayorder.
 */
	$lastdisplay = -1;
	$query = "SELECT * from report_select where (report = '$type') order by `displayorder`";
	$stat = rlc_mysql_get_table($sql, $res, $query);
	echo "<br>Report: <b>$type</b><br>";
	echo "<br><b>--- Selection Criteria ---</b><br>";
	rlc_web_start_prompt();
	while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
	{
	    if ($row['displayorder'] == $lastdisplay)
	    {
	    	continue;
	    }
	    $lastdisplay = $row['displayorder'];
	    if ($row['fixed']) 
	    {
		rlc_web_hidden($row['var']."_op", $row['op']);
		rlc_web_hidden($row['var'], $row['value']);
	    }
	    else
	    {
		if (!strcmp($row['var'], "product_id"))
		{
			$numproducts = rlc_mysql_read_products($sql, $products, 
	    					$lictype, $license, $version,
						$version_type, 
						$active, $obsolete, "", "", 1);
			if ($numproducts > 0)
			{
				rlc_web_prompt_product_id(1, $numproducts, 
							$products, $active, 
							$obsolete, 1, 1, 
							$row['display']);
			}
		}
		else
		{
	    		rlc_web_prompt($row['display'], "", $row['var'], 
								$row['size']);
		}

	    }
	}
}


/************************************************************************/
/*
 *	RLC reports - main generate report code
 */
	$perm = check_user($session);
	rlc_web_rpp("rlc-rpp-rpt");
	rlc_web_header(RLC_MENU_REPORTS, $session);

	if (array_key_exists('report_type', $_POST))
	{
		$report_type       = $_POST['report_type'];
		$gettype = 0;
		$generate_report = 0;
		if (array_key_exists('generate_report', $_POST))
		{
			$generate_report = 1;
		}
		if (array_key_exists('rep_output', $_POST))
		{
			$rep_output = $_POST['rep_output'];
		}
		else
		{
			$rep_output = 0;
		}
	}
	else if (array_key_exists('report_type', $_GET))
	{
		$report_type       = $_GET['report_type'];
		$gettype = 0;
		$generate_report = 0;
		if (array_key_exists('generate_report', $_GET))
		{
			$generate_report = 1;
		}
		if (array_key_exists('rep_output', $_GET))
		{
			$rep_output = $_GET['rep_output'];
		}
		else
		{
			$rep_output = 0;
		}
	}
	else
	{
		$gettype = 1;
	}

	if (!$perm['act_view_enabled'])
	{
		rlc_noview($session);
		finish_page(0, 0, 0);
	}
	else
	{
	    rlc_web_title("Activation Report Generator", "rlc_report.php");

	    $sql = rlc_mysql_init($isv);

	    if ($gettype)
	    {
/*
 *	    	Read the report definitions, and prompt for the desired report
 */
		$query = "SELECT * from report_types order by `displayorder`";
		$stat = rlc_mysql_get_table($sql, $res, $query);
		$i = 0;
		$report_types = array();
		while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
		{
			$report_types[$i] = $row['report'];
			$i++;
		}
	    	rlc_web_prompt_report_type(0, $report_types);
		mysqli_free_result($res);
	    	rlc_mysql_close($sql);
	    	finish_page("Select Report Parameters", 0, 0);
	    }
	    else if ($generate_report == 0)
	    {
/*
 *		Get selection criteria
 */
		get_selection_data($sql, $report_type);
		rlc_web_hidden("report_type", $report_type);
		rlc_web_hidden("rep_output", $rep_output);
		rlc_web_hidden("generate_report", "1");
	    	rlc_mysql_close($sql);
	    	finish_page("Generate Report", "Back", "rlc_report.php");
	    }
	    else
	    {
/*
 *		Generate the report
 */

		if (array_key_exists('report_type', $_GET))
		{
			$report_type = $_GET['report_type'];
		}
		else
		{
			$report_type = $_POST['report_type'];
		}

		$extra = "";
		$pxtra = "";
		if ($rep_output == 0)
		{
			$o = sprintf("%d", $rep_output);
			$extra = "generate_report=1&rep_output=".$o.
						"&report_type=".$report_type;
			$pxtra = "<input type=hidden name=generate_report value=1>".
				 "<input type=hidden name=rep_output value=$o>".
				 "<input type=hidden name=report_type value='$report_type'>";
		}
		do_report($sql, $report_type, $report_type, $rep_output,
							$extra, $pxtra, $_POST);
		finish_page_extra(0, "Back", "rlc_report.php", 0, 0, 0, 0, "", 
									"");
	    }
	}
?>
