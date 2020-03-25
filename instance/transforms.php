<?php
/* most routines in this program are found in the the leaflet:
[Geomatics, Guidance Note Number 7, part 2
Coordinate Conversions and Transformations including Formulas
Revised - June 2013]
issued by the International Association of Oil and Gas Producers

The routines for HTRS07 transformations are found in:
[ΜΟΝΤΕΛΟ ΜΕΤΑΣΧΗΜΑΤΙΣΜΟΥ
ΣΥΝΤΕΤΑΓΜΕΝΩΝ ΜΕΤΑΞΥ ΤΟΥ ΣΥΣΤΗΜΑΤΟΣ
ΑΝΑΦΟΡΑΣ ΤΟΥ HEPOS (HTRS07)
ΚΑΙ ΤΟΥ ΕΛΛΗΝΙΚΟΥ ΓΕΩ∆ΑΙΤΙΚΟΥ
ΣΥΣΤΗΜΑΤΟΣ ΑΝΑΦΟΡΑΣ (ΕΓΣΑ87)]
compiled by the Τμήμα Αγρονόμων και Τοπογράφων Μηχανικών του ΑΠΘ for ΚΤΗΜΑΤΟΛΟΓΙΟ ΑΕ.
Please visit www.hepos.gr
HATT coefficients were copied from an excel file hatt2egsa.xls compiled by Γιαννίρης Γιάννης - yyanniris@tee.gr, thanks Γιάννη
Demetrius Papademetriou, May 2014 (demkpap@gmail.com)
*/
/********/

function read_coefficients()
//reads the coefficients of each HATT 1:50000 map from file 'hatt2egsa.txt
{
	$coefs=array();
	$lines=file('hatt2egsa.txt');
	for ($i=1,$len=count($lines);$i<$len;$i++) {		//skip 1st line
		$xline = explode(",",$lines[$i]);
		list($coefs[$i-1]['phi'], $coefs[$i-1]['lambda'], $coefs[$i-1]['aa'], $coefs[$i-1]['name'], $coefs[$i-1]['a0'], $coefs[$i-1]['a1'], $coefs[$i-1]['a2'], $coefs[$i-1]['a3'], $coefs[$i-1]['a4'], $coefs[$i-1]['a5'], $coefs[$i-1]['b0'], $coefs[$i-1]['b1'], $coefs[$i-1]['b2'], $coefs[$i-1]['b3'], $coefs[$i-1]['b4'], $coefs[$i-1]['b5'],$coefs[$i-1]['quadrant'])=$xline;
	}
	return($coefs);

}

/********/

function find_fyllo($xi,$psi)
//Find the map(s)  where the egsa87 coordinates $xi $psi exist. Normally there are more than one maps since this routine uses
//the smaller distance of this point to all centers of the maps.
//Since the centers could differ by a few meters this routine considers a distance of +5 meters as being equal to the minimum
{
global $h2e, $hatt_no;
$len=count($h2e);
$minvalue=1e10;
$mltemp=array();		//Temporary array to keep map nos and distances
$ml=array();			//keeps all distances that are +-5 meters from minimun distace
$dist=array();			//keeps all distances of centers of maps from egsa87 point
if ($hatt_no =="0") {		//no map is assigned, so search all
	for ($i=0;$i<$len;$i++) $dist[]=sqrt(($h2e[$i]['a0']-$xi)*($h2e[$i]['a0']-$xi)+($h2e[$i]['b0']-$psi)*($h2e[$i]['b0']-$psi));
	$mmin=min($dist);
	for ($i=0;$i<$len;$i++) if ($dist[$i]> $mmin-5 && $dist[$i]<$mmin+5) $mltemp[]=array($i,$dist[$i]);	//collect all distances +-5
	usort($mltemp, build_sorter(1));
	//Now sort the array according to the $dist value
	for($i=0;$i<count($mltemp);$i++) $ml[]=$mltemp[$i][0];
	//	$ml=array_column($ml,0);
	//$ml is now contains the maps clessified according to their distsnces from centers
	//Now the centre of map has been found and the maps that have the same centre is the $ml array
	//name of maps are {$h2e[$ml]['name']}
}
else {	//map is defined so everything should be calculated relative to center of this map
	for ($i=0;$i<$len;$i++) {
		if ($hatt_no==$h2e[$i]['name'])  {
			$dist[]=sqrt(($h2e[$i]['a0']-$xi)*($h2e[$i]['a0']-$xi)+($h2e[$i]['b0']-$psi)*($h2e[$i]['b0']-$psi));
			$mmin=$dist[0];
			$ml[]=$i;
			break;
		}
	}
}
//
$cx=$h2e[$ml[0]]['a0'];
$cy=$h2e[$ml[0]]['b0'];
$mc=0;
$hattx=$xi-$cx;
$hatty=$psi-$cy;
//echo "<br/>hattx:$hattx, hatty:$hatty\n";
//echo "hattx:$hattx ******* hatty:$hatty<br/>";
/*
print_r($ml);
echo "---<br/>---";
print_r($dist);
exit;
*/
while(true) {

//	if (abs($hattx)<0.000001 && abs($hatty)<0.000001) break;
//	X = X0 + A1x + B1y + Γ1x2 + Δ1y2 + E1xy
//Y = Y0 + A2x + B2y + Γ2x2 + Δ2y2 + E2xy
	$cx=$h2e[$ml[0]]['a0'] + $h2e[$ml[0]]['a1']*$hattx + $h2e[$ml[0]]['a2']*$hatty + $h2e[$ml[0]]['a3']*$hattx*$hattx + $h2e[$ml[0]]['a4']*$hatty*$hatty + $h2e[$ml[0]]['a5']*$hattx*$hatty;
	$cy=$h2e[$ml[0]]['b0'] + $h2e[$ml[0]]['b1']*$hattx + $h2e[$ml[0]]['b2']*$hatty + $h2e[$ml[0]]['b3']*$hattx*$hattx + $h2e[$ml[0]]['b4']*$hatty*$hatty + $h2e[$ml[0]]['b5']*$hattx*$hatty;
//	echo "<br/>cx:$cx, cy:$cy\n";
	$hattx+=$xi-$cx;
	$hatty+=$psi-$cy;
	if(abs($xi-$cx)<0.0001 && abs($psi-$cy)<0.0001) break;
//echo "<br/>hattx:$hattx, hatty:$hatty\n";
//echo "hattx:$hattx------hatty:$hatty<br/>";
	$mc++;
	if ($mc>17) break;

}
/*
print_r($h2e[$ml[0]]);
echo "hatx $hattx haty: $hatty \n";
echo "<br/>".$h2e[$ml[0]]['quadrant']."444";
exit;
*/
//here the true coordinates relative to the center of map has been fount. Check the sign of coordinates to find
//in which quadrant the coordinates are
//print_r($ml);
$highlight="";
//here below the signs of coordinates are used to find to which quandrant of map this point is, if possible
if ($hattx>=0 && $hatty>=0) {
	for($i=0;$i<count($ml);$i++) {
		if (strpos($h2e[$ml[$i]]['quadrant'],"1")!==false) {
			$highlight=$h2e[$ml[$i]]['name'];
			break;
			}
	}
}
else if ($hattx<0 && $hatty>=0) {
	for($i=0;$i<count($ml);$i++) {
		if (strpos($h2e[$ml[$i]]['quadrant'],"2")!==false) {
			$highlight=$h2e[$ml[$i]]['name'];
			break;
			}
	}
}
else if ($hattx<0 && $hatty<0) {
	for($i=0;$i<count($ml);$i++) {
		if (strpos($h2e[$ml[$i]]['quadrant'],"3")!==false) {
			$highlight=$h2e[$ml[$i]]['name'];
			break;
			}
	}
}
else if ($hattx>=0 && $hatty<0) {
	for($i=0;$i<count($ml);$i++) {
		if (strpos($h2e[$ml[$i]]['quadrant'],"4")!==false) {
			$highlight=$h2e[$ml[$i]]['name'];
			break;
			}
	}
}
for($i=0,$names="";$i<count($ml);$i++)
$names.= ($highlight==$h2e[$ml[$i]]['name']?"[-->".$h2e[$ml[$i]]['name']."<--] ":"[".$h2e[$ml[$i]]['name']."] ");

return array($hattx, $hatty,$names);
//echo "<br/>x:$hattx, y:$hatty\n";
//exit;

}

/*******/

function from_HATT_to_EGSA87($myxi, $myyi, $maps)
//$myxi, $myyi are HATT coordinates and $maps is a string of candidate maps.
//If there are more than one maps it uses the highlighted map, or, it it dows not exist the first map
//returns array(x,y) in EGSA87 or false
{
global $h2e;
$maps=trim($maps);
//echo "|*****$maps*******|";
if (empty($maps)) return false;
//maps not empty
$fyllo_xarti=str_replace("[","",$maps); //get rid of opening bracket
$farr=explode("]",$fyllo_xarti);
$fyllo_xarti=str_replace(array("<--","-->"),"",$farr[0]);   //Fyllo xarti is either the first fyllo or
//		the higlighted one, if it exists
//print_r($farr);
for ($i=1;$i<count($farr);$i++) if(strpos($farr[$i],"--")!==false) $fyllo_xarti=trim(str_replace(array("<--","-->"),"",$farr[$i]));
//echo "**|$fyllo_xarti||||";
for($i=0,$len=count($h2e),$found=0;$i<$len;$i++) {
	if ($fyllo_xarti==trim($h2e[$i]['name'])) {
		$found=1;
		$xi=$h2e[$i]['a0'] + $h2e[$i]['a1']*$myxi + $h2e[$i]['a2']*$myyi + $h2e[$i]['a3']*$myxi*$myxi + $h2e[$i]['a4']*$myyi*$myyi + $h2e[$i]['a5']*$myxi*$myyi;
		$psi=$h2e[$i]['b0'] + $h2e[$i]['b1']*$myxi + $h2e[$i]['b2']*$myyi + $h2e[$i]['b3']*$myxi*$myxi + $h2e[$i]['b4']*$myyi*$myyi + $h2e[$i]['b5']*$myxi*$myyi;
		break;
	}
//	else echo "|$fyllo_xarti| not equal to |". trim($h2e[$i]['name'])."|<br/>";
}
if ($found==1)	return array($xi,$psi);
else return false;
}

/*******/

function wgs84xy_to_egsa87xy($x,$y,$z)
{
return(array ($x+199.723, $y-74.03, $z-246.018));
}

/********/

function egsa87xy_to_wgs84xy($x,$y,$z)
{
return(array ($x-199.723, $y+74.03, $z+246.018));
}


/*******/

function to_proj_xy_from_fl($type,$ff,$ll)
//given φ,λ in greek egsa87 this function calulates the UTM projection x,y
//the $type can be either EGSA87 or HTRS07 which differ only in the False Northing value
{
$phi=deg2rad($ff);
$lambda=deg2rad($ll);
//$a=6377563.396;			//airy
//$f=1/299.32496;	//Airy
$a=6378137;  //equatorial radius
$f=1/298.257222100882711;
$e=sqrt(2*$f-$f*$f);
$l_orig=24*M_PI/180;				//Longitude of natural origin
$f_orig=0*M_PI/180;				//Latitude of natural origin
$k_orig=0.9996;			//Scale factor at natural origin
$FE=500000;							//False easting
if ($type=="EGSA87") $FN=0;			//False northing
else if($type=="HTRS07") $FN=-2000000;


//$b=6356752.314140347;  //grs80: polar radius
//$f=($a-$b)/$a;

$n=$f/(2-$f);
$B=$a/(1+$n)*(1+$n*$n/4+$n*$n*$n*$n/64);
$h1=$n/2-2/3*$n*$n+5/16*$n*$n*$n+41/180*$n*$n*$n*$n;
$h2=13/48*$n*$n-3/5*$n*$n*$n+557/1440*$n*$n*$n*$n;
$h3=61/240*$n*$n*$n-103/140*$n*$n*$n*$n;
$h4=49561/161280*$n*$n*$n*$n;
//meridional arc distance from equator to projection origin (Mo)
$Qo=asinh(tan($f_orig))-$e*atanh($e * sin($f_orig));
$bo=atan(sinh($Qo));

$ksio0=asin(sin($bo));
$ksio1=$h1*sin(2*$ksio0);
$ksio2=$h2*sin(4*$ksio0);
$ksio3=$h3*sin(6*$ksio0);
$ksio4=$h4*sin(8*$ksio0);
$ksio=$ksio0+$ksio1+$ksio2+$ksio3+$ksio4;
$Mo=$B*$ksio;
$Q=asinh(tan($phi))-$e*atanh($e*sin($phi));
$beta=atan(sinh($Q));

$ita0=atanh(cos($beta)*sin($lambda-$l_orig));
$ksi0=asin(sin($beta)*cosh($ita0));

$ksi1=$h1*sin(2*$ksi0)*cosh(2*$ita0);
$ita1=$h1*cos(2*$ksi0)*sinh(2*$ita0);

$ksi2=$h2*sin(4*$ksi0)*cosh(4*$ita0);
$ita2=$h2*cos(4*$ksi0)*sinh(4*$ita0);

$ksi3=$h3*sin(6*$ksi0)*cosh(6*$ita0);
$ita3=$h3*cos(6*$ksi0)*sinh(6*$ita0);

$ksi4=$h4*sin(8*$ksi0)*cosh(8*$ita0);
$ita4=$h4*cos(8*$ksi0)*sinh(8*$ita0);
$ksi=$ksi0+$ksi1+$ksi2+$ksi3+$ksi4;
$ita=$ita0+$ita1+$ita2+$ita3+$ita4;

$easting=$FE+$k_orig*$B*$ita;
$northing=$FN+$k_orig*($B*$ksi-$Mo);

return(array($easting,$northing));


}

/*******/

function to_fl_from_proj_xy($type, $east,$north)
//given x,y (east,north in greek UTM projection this function calulates the f,l on greek ellipsoid (GRS80)
//the $type can be either EGSA87 or HTRS07 which differ only in the False Northing value
{
$a=6378137;  //equatorial radius
$f=1/298.257222100882711;
//$a=6377563.396;			//airy
//$f=1/299.32496;	//Airy
$e=sqrt(2*$f-$f*$f);
$l_orig=24*M_PI/180;				//Longitude of natural origin
$f_orig=0*M_PI/180;				//Latitude of natural origin
$k_orig=0.9996;			//Scale factor at natural origin
$FE=500000;							//False easting
if ($type=="EGSA87") $FN=0;			//False northing
else if($type=="HTRS07") $FN=-2000000;

//$b=6356752.314140347;  //grs80: polar radius
//$f=($a-$b)/$a;

$n=$f/(2-$f);
$B=$a/(1+$n)*(1+$n*$n/4+$n*$n*$n*$n/64);

$h1=$n/2-2/3*$n*$n+5/16*$n*$n*$n+41/180*$n*$n*$n*$n;
$h2=13/48*$n*$n-3/5*$n*$n*$n+557/1440*$n*$n*$n*$n;
$h3=61/240*$n*$n*$n-103/140*$n*$n*$n*$n;
$h4=49561/161280*$n*$n*$n*$n;

//meridional arc distance from equator to projection origin (Mo)
$Qo=asinh(tan($f_orig))-$e*atanh($e * sin($f_orig));
$bo=atan(sinh($Qo));
$ksio0=asin(sin($bo));
$ksio1=$h1*sin(2*$ksio0);
$ksio2=$h2*sin(4*$ksio0);
$ksio3=$h3*sin(6*$ksio0);
$ksio4=$h4*sin(8*$ksio0);
$ksio=$ksio0+$ksio1+$ksio2+$ksio3+$ksio4;
$Mo=$B*$ksio;
//up to here the same as toxy

$h1t=$n/2-2/3*$n*$n+37/96*$n*$n*$n-1/360*$n*$n*$n*$n;
$h2t=1/48*$n*$n+1/15*$n*$n*$n-437/1440*$n*$n*$n*$n;
$h3t=17/480*$n*$n*$n-37/840*$n*$n*$n*$n;
$h4t=4397/161280*$n*$n*$n*$n;
$itat=($east-$FE)/($B*$k_orig);
$ksit=($north-$FN+$k_orig*$Mo)/($B*$k_orig);
$ksi1t=$h1t*sin(2*$ksit)*cosh(2*$itat);
$ita1t=$h1t*cos(2*$ksit)*sinh(2*$itat);
$ksi2t=$h2t*sin(4*$ksit)*cosh(4*$itat);
$ita2t=$h2t*cos(4*$ksit)*sinh(4*$itat);
$ksi3t=$h3t*sin(6*$ksit)*cosh(6*$itat);
$ita3t=$h3t*cos(6*$ksit)*sinh(6*$itat);
$ksi4t=$h4t*sin(8*$ksit)*cosh(8*$itat);
$ita4t=$h4t*cos(8*$ksit)*sinh(8*$itat);
$ksi0t=$ksit-($ksi1t+$ksi2t+$ksi3t+$ksi4t);
$ita0t=$itat-($ita1t+$ita2t+$ita3t+$ita4t);
$vitat=asin(sin($ksi0t)/cosh($ita0t));
$Qt=asinh(tan($vitat));
$Qtt=$Qt+$e*atanh($e*tanh($Qt));
do {
	$Qtt_previous=$Qtt;
	$Qtt=$Qt+$e*atanh($e*tanh($Qtt));
} while(abs($Qtt-$Qtt_previous)>1e-12);
$phi=atan(sinh($Qtt));
$lambda=$l_orig+asin(tanh($ita0t)/cos($vitat));
return(array(rad2deg($phi),rad2deg($lambda)));


}

/*******/

function laea_to_fl_from_proj_xy($east,$north)
//Lambert_Azimuthal_Equal_Area projection for GRS80
//it is not used in this program
{
$a=6378137;  						//equatorial radius GRS80
$f=1/298.257222100882711; 		//GRS80
$FE=4321000;
$FN=3210000;
$LoO=52;								//Latidute of Origin
$CM=10;								//Central Meridian
//$phi=deg2rad($phi);
//$lambda=deg2rad($lambda);
$phio=deg2rad($LoO);
$lambdao=deg2rad($CM);
$e2=2*$f-$f*$f;
$e=sqrt(2*$f-$f*$f);
$qp=(1-$e2)*(1/(1-$e2)-1/(2*$e)*log((1-$e)/(1+$e)));
$qo=(1-$e2)*(sin($phio)/(1-$e2*sin($phio)*sin($phio))-1/(2*$e)*log( (1-$e*sin($phio))/(1+$e*sin($phio))));
//$q=(1-$e2)*(sin($phi)/(1-$e2*sin($phi)*sin($phi))-1/(2*$e)*log( (1-$e*sin($phi))/(1+$e*sin($phi))));
$betao=asin($qo/$qp);
//$beta=asin($q/$qp);
$Rq=$a* sqrt($qp/2);
$D=$a*cos($phio)/sqrt(1-$e2*sin($phio)*sin($phio))/$Rq/cos($betao);
$rho=sqrt(($east-$FE)/$D*($east-$FE)/$D+$D*($north-$FN)*$D*($north-$FN));
$C=2*asin($rho/(2*$Rq));
$betat=asin(  cos($C)*sin($betao) + $D*($north-$FN)*sin($C)*cos($betao)/$rho);
$lambda=$lambdao+atan( ($east-$FE)*sin($C)/ ($D*$rho*cos($betao)*cos($C)-$D*$D*($north-$FN)*sin($betao)*sin($C)));
$phi=$betat+($e2/3 + 31*$e2*$e2/180+517*$e2*$e2*$e2/5040)*sin(2*$betat)+(23*$e2*$e2/360+251*$e2*$e2*$e2/3780)*sin(4*$betat) + (761*$e2*$e2*$e2/45360)*sin(6*$betat);
//echo "\nIn function:".__FUNCTION__."\n";
//print_r( get_defined_vars());
return array(rad2deg($phi),rad2deg($lambda));
}

/*******/

function laea_to_proj_xy_from_fl($phi,$lambda)
//Lambert_Azimuthal_Equal_Area projection for GRS80
//it is not used in this program
{
$a=6378137;  						//equatorial radius GRS80
$f=1/298.257222100882711; 		//GRS80
$FE=4321000;
$FN=3210000;
$LoO=52;								//Latidute of Origin
$CM=10;								//Central Meridian
$phi=deg2rad($phi);
$lambda=deg2rad($lambda);
$phio=deg2rad($LoO);
$lambdao=deg2rad($CM);
$e2=2*$f-$f*$f;
$e=sqrt(2*$f-$f*$f);
$qp=(1-$e2)*(1/(1-$e2)-1/(2*$e)*log((1-$e)/(1+$e)));
$qo=(1-$e2)*(sin($phio)/(1-$e2*sin($phio)*sin($phio))-1/(2*$e)*log( (1-$e*sin($phio))/(1+$e*sin($phio))));
$q=(1-$e2)*(sin($phi)/(1-$e2*sin($phi)*sin($phi))-1/(2*$e)*log( (1-$e*sin($phi))/(1+$e*sin($phi))));
$betao=asin($qo/$qp);
$beta=asin($q/$qp);
$Rq=$a* sqrt($qp/2);
$D=$a*cos($phio)/sqrt(1-$e2*sin($phio)*sin($phio))/$Rq/cos($betao);
$B=$Rq* sqrt(2/(1+sin($betao)*sin($beta)+(cos($betao)*cos($beta)*cos($lambda-$lambdao))));
$east=$FE+$B*$D*cos($beta)*sin($lambda-$lambdao);
$north=$FN+$B/$D*(cos($betao)*sin($beta)-sin($betao)*cos($beta)*cos($lambda-$lambdao));
//echo "\nIn function:".__FUNCTION__."\n";
//print_r( get_defined_vars());

return array($east,$north);
}

/*******/

function flh2xyz($ellipsoid,$phi,$lambda,$h)
//convert phi, lambda, h  to x,y,z - h height above ellipsoid surface
//EGSA87 and HTRS07 ellipsoids are the same and differ only by a fraction of a millimeter to WGS84 ellipsoid in small semi-axis
{
$phi=deg2rad($phi);
$lambda=deg2rad($lambda);
$a=6378137;  //equatorial radius
if($ellipsoid=="WGS84") $f = 1/298.257223563;
else if ($ellipsoid=="EGSA87" || $ellipsoid=="HTRS07") $f = 1/298.257222100882711243;
$e2=2*$f-$f*$f;
//$epsilon=$e2/(1-$e2);
$greekni=$a/sqrt(1-$e2*sin($phi)*sin($phi));  //prime vertical radius of curvature at latitude φ
$X=($greekni+$h)*cos($phi)*cos($lambda);
$Y=($greekni+$h)*cos($phi)*sin($lambda);
$Z=((1-$e2)*$greekni+$h)*sin($phi);
//echo "\nIn function:".__FUNCTION__."\n";
//print_r( get_defined_vars());
return(array($X,$Y,$Z));
}

/*******/

function xyz2flh($ellipsoid,$xo,$yo,$zo)
//convert x,y,z to phi, lambda, h
//EGSA87 and HTRS07 ellipsoids are the same and differ only by a fraction of a millimeter to WGS84 ellipsoid in small semi-axis
{
$a=6378137;  //equatorial radius
if($ellipsoid=="WGS84") $f = 1/298.257223563;
else if ($ellipsoid=="EGSA87" || $ellipsoid=="HTRS07") $f = 1/298.257222100882711243;
$e2=2*$f-$f*$f;
$epsilon=$e2/(1-$e2);
$bi=$a*(1-$f);
$p=sqrt($xo*$xo+$yo*$yo);
$q=atan($zo*$a/($p*$bi));
$phi=atan(($zo+$epsilon*$bi*sin($q)*sin($q)*sin($q))/($p-$e2*$a*cos($q)*cos($q)*cos($q)));
$greekni=$a/sqrt(1-$e2*sin($phi)*sin($phi));  //prime vertical radius of curvature at latitude φ
$lambda=atan($yo/$xo);
$h=$p/cos($phi)-$greekni;
//echo "\nIn function:".__FUNCTION__."\n";
//print_r( get_defined_vars());
return(array(rad2deg($phi),rad2deg($lambda),$h));
}


/*******/

function WGS84_to_EGSA87($phi,$lambda)
//converts φ,λ of WGS84 to EGSA87 projection x,y (easting, northing)
{
//$phi=deg2rad($phi);
//$lambda=deg2rad($lambda);

$temparr=flh2xyz("WGS84",$phi,$lambda,0);//covert to geocentric xyz

$temparr=wgs84xy_to_egsa87xy($temparr[0],$temparr[1],$temparr[2]);

$temparr=xyz2flh("EGSA87",$temparr[0],$temparr[1],$temparr[2]);

$temparr=to_proj_xy_from_fl("EGSA87",$temparr[0],$temparr[1],$temparr[2]);

return($temparr);
}

/*******/

function EGSA87_to_WGS84($xi,$psi) {
//converts x,y (easting, northing) of  EGSA87 projection to φ,λ of WGS84
$temparr=to_fl_from_proj_xy("EGSA87", $xi,$psi);
$temparr=flh2xyz("EGSA87",$temparr[0],$temparr[1],0);
$temparr=egsa87xy_to_wgs84xy($temparr[0],$temparr[1],$temparr[2]);
$temparr=xyz2flh("WGS84",$temparr[0],$temparr[1],$temparr[2]);
return array($temparr[0],$temparr[1]);
}

/********/

function HTRS07_xyz_to_EGSA87_xyz($x,$y,$z)
//converts the cartesian coordinates x,y,z of HTRS07 ellipsoid to cartesian coordinates of EGSA87 ellipsoid (relative to center of ellipsoid)
//the 7 step calculation is used because the HTRS ellipsoid is moved and rotated in relation to EGSA87 one
{
	$tx=203.437;
	$ty=-73.461;
	$tz=-243.594;
	$ex=deg2rad(-0.17/3600);
	$ey=deg2rad(-0.06/3600);
	$ez=deg2rad(-0.151/3600);
	$ds=-0.294/1000000;
	$x87=$x+$tx+($ds*$x+$ez*$y-$ey*$z);
	$y87=$y+$ty+(-$ez*$x+$ds*$y+$ex*$z);
	$z87=$z+$tz+($ey*$x-$ex*$y+$ds*$z);
	return array($x87,$y87,$z87);
}

/*******/


function EGSA87_xyz_to_HTRS07_xyz($x,$y,$z)
//the reverse of above transormation
{
	$tx=-203.437;
	$ty=73.461;
	$tz=243.594;
	$ex=deg2rad(0.17/3600);
	$ey=deg2rad(0.06/3600);
	$ez=deg2rad(0.151/3600);
	$ds=0.294/1000000;
	$x07=$x+$tx+($ds*$x+$ez*$y-$ey*$z);
	$y07=$y+$ty+(-$ez*$x+$ds*$y+$ex*$z);
	$z07=$z+$tz+($ey*$x-$ex*$y+$ds*$z);
	return array($x07,$y07,$z07);
}

/*******/

function check_data_files()
//Checks the existence of datafiles for correction factors (HTRS07)
//returns the 2 file handlers and the some data to use the data files
//also recreates the index file if it does not exist or is older that ether file
{
	$efile="dE_2km_V1-0.grd";
	$nfile="dN_2km_V1-0.grd";
	$indexfile="data.ndx";
	if (!file_exists($efile)) die ("Data file ".$efile."does not exist. Dying....");
	if (!file_exists($nfile)) die ("Data file ".$nfile."does not exist. Dying....");
	$ef=fopen($efile,"r");
	$nf=fopen($nfile,"r");
	if (!$ef || !$nf) die ("Data files could not be opened. Dying....");

	fseek($ef,0);
	fseek($nf,0);
	$grammes=trim(fgets($ef,1024));
	if ($grammes!=trim(fgets($nf))) die("Data files do not match in line 1\n");
	$stiles=trim(fgets($ef,1024));
	if ($stiles!=trim(fgets($nf))) die("Data files do not match in line 2\n");
	$vima=trim(fgets($ef,1024));
	if ($vima!=trim(fgets($nf))) die("Data files do not match in line 3\n");
	$low_y=trim(fgets($ef,1024));
	if ($low_y!=trim(fgets($nf))) die("Data files do not match in line 4\n");
	$low_x=trim(fgets($ef,1024));
	if ($low_x!=trim(fgets($nf))) die("Data files do not match in line 5\n");
	if (  !file_exists($indexfile) || filemtime($indexfile) < filemtime($efile) || filemtime($indexfile) < filemtime($nfile)) {
		//build index
		$index=fopen($indexfile,"w");
		while (!feof($ef) && !feof($nf)){
			fprintf($index,"%d,%d\n", ftell($ef), ftell($nf));
			fgets($ef);
			fgets($nf);
		}
	fclose($index);
	}
	rewind($ef);
	rewind($nf);
	return array($ef,$nf,$vima,$low_x,$low_y);
}

/*******/

function find_correction_factors($x,$y)
//finds the correction factor δΕ, δΝ for coordnates in HTRS07 using the files dE_2km_V1-0.grd and dN_2km_V1-0.grd given by HEPOS
{
//	global $low_x,$low_y,$vima;
/*	if ($destination=="EGSA87") {
		$low_x=41851;
		$low_y=3845711;
	}
	if ($destination=="HTRS07") {
		$low_x=41600;
		$low_y=1845619;
	}
	*/
	if ($x<$_SESSION['low_x'] || $y<$_SESSION['low_y']) return array(0,0);
	$_SESSION['offs']=file("data.ndx",FILE_IGNORE_NEW_LINES);
	$grammi= floor(($y-$_SESSION['low_y'])/$_SESSION['vima']); 		//This row and the next cotain the values in both data files
	if ($grammi>count($_SESSION['offs'])-1) return array(0,0); //coordinate out of bounds: zero correction
	$rest_y=$y-$_SESSION['low_y']-$grammi*$_SESSION['vima'];
	$stili=floor(($x-$_SESSION['low_x'])/$_SESSION['vima']);			//This column and the next contain the values in both data files
	$rest_x=$x-$_SESSION['low_x']-$stili*$_SESSION['vima'];
//	$ef=fopen("dE_2km_V1-0.grd","r");
//	$nf=fopen("dN_2km_V1-0.grd","r");
	list($east_off,$north_off)=explode(",",$_SESSION['offs'][$grammi]);
	fseek($_SESSION['ef'],$east_off,SEEK_SET);
	$first=explode (" ", preg_replace("/[ \t]+/"," ",trim(fgets($_SESSION['ef']))));
	$second=explode (" ", preg_replace("/[ \t]+/"," ",trim(fgets($_SESSION['ef']))));
	if($stili>count($first)-2 || $stili>count($second)-2)  return array(0,0); //coordinate out of bounds: zero correction
	$ll=$first[$stili];
	$lr=$first[$stili+1];
	$ul=$second[$stili];
	$ur=$second[$stili+1];

	$upper=$ul+$rest_x*($ur-$ul)/$_SESSION['vima'];
	$lower=$ll+$rest_x*($lr-$ll)/$_SESSION['vima'];
	$correction_x=$lower+$rest_y*($upper-$lower)/$_SESSION['vima'];
/*
	unset($first);
	unset($second);
	unset($offs);
	print_r( get_defined_vars());
	*/
	fseek($_SESSION['nf'],$north_off,SEEK_SET);
	$first=explode (" ",preg_replace("/[ \t]+/"," ",trim(fgets($_SESSION['nf']))));
	$second=explode (" ",preg_replace("/[ \t]+/"," ",trim(fgets($_SESSION['nf']))));
	if($stili>count($first)-2 || $stili>count($second)-2)  return array(0,0); //coordinate out of bounds: zero correction
	$ll=$first[$stili];
	$lr=$first[$stili+1];
	$ul=$second[$stili];
	$ur=$second[$stili+1];
	$upper=$ul+$rest_x*($ur-$ul)/$_SESSION['vima'];
	$lower=$ll+$rest_x*($lr-$ll)/$_SESSION['vima'];
	$correction_y=$lower+$rest_y*($upper-$lower)/$_SESSION['vima'];
//	fclose($ef);
//	fclose($nf);

	return array($correction_x/100,$correction_y/100);


}

/*******/

function EGSA87_to_HTRS07($xi,$psi) {
//converts projection $xi,$psi in EGSA 87 to projection in HTRS07
//with correction factors
$temparr=to_fl_from_proj_xy("EGSA87", $xi,$psi);
$temparr=flh2xyz("EGSA87",$temparr[0],$temparr[1],0);
$temparr=EGSA87_xyz_to_HTRS07_xyz($temparr[0],$temparr[1],$temparr[2]);
$temparr=xyz2flh("EGSA87",$temparr[0], $temparr[1], $temparr[2]);
$temparr=to_proj_xy_from_fl("HTRS07",$temparr[0],$temparr[1]);
$newarr=find_correction_factors($temparr[0],$temparr[1]);
return array($temparr[0]-$newarr[0],$temparr[1]-$newarr[1]);
}

/******/

function HTRS07_to_EGSA87($xi,$psi)
//converts projection $xi,$psi in HTRS07 to projection in EGSA 87
//with correction factors

{
$temparrr=to_fl_from_proj_xy("HTRS07",$xi,$psi);
$newarr=find_correction_factors($xi,$psi);   //find corrections now
//print_r($newarr);
$temparrr=flh2xyz("HTRS07",$temparrr[0],$temparrr[1],0);
$temparrr=HTRS07_xyz_to_EGSA87_xyz($temparrr[0],$temparrr[1],$temparrr[2]);
$temparrr=xyz2flh("EGSA87",$temparrr[0], $temparrr[1], $temparrr[2]);
$temparrr=to_proj_xy_from_fl("EGSA87",$temparrr[0],$temparrr[1]);
return array($temparrr[0]+$newarr[0],$temparrr[1]+$newarr[1]);
//$newarr=find_correction_factors($myarr[0]-$newarr[0],$myarr[1]-$newarr[1]);
//print_r($newarr);

}


$temp = WGS84_to_EGSA87($argv[1],$argv[2]);
print strval($temp[0]) . "," . strval($temp[1])  ;

?>
