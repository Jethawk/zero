<?php 
namespace B7KP\Utils;

use B7KP\Utils\Constants as C;
use B7KP\Utils\Functions as F;
use B7KP\Library\Url; 
use B7KP\Library\Lang; 
use B7KP\Library\Route; 

class Snippets
{
	static function recentListRow($name, $img, $artist, $album, $url, $login)
	{
		if(empty($img))
		{
			$img = Url::asset("img/default-alb.png");
		}
		if(!empty($album))
		{
			$album = "<small class='text-muted'>".$album."</small>
				<br>";
		}

		$url = Route::url("lib_mus", array("name" => F::fixLFM($name), "artist" => F::fixLFM($artist), "login" => $login));
		
		return "
		<div class='row bottomspace-xs'>
			<div class='col-xs-3'>
				<img class='img-responsive' src='{$img}' alt='{$name}'>
			</div>
			<div class='col-xs-9'>
				<a href={$url} target='_blank'>{$name}</a>
				<br>
				".$album."
				<small>{$artist}</small>
			</div>
		</div>
		";
	}

	static function getRankColor($rank, $peak)
	{
		if($rank == $peak)
		{
			return "no-one";
		}
	}

	static function topActListRow($name, $url, $playcount, $img, $biggest, $login)
	{
		$perc = $playcount/$biggest*100;
		$url = Route::url("lib_art", array("name" => F::fixLFM($name), "login" => $login));
		$img = "
			<div class='col-xs-3 getimage imgfix' id='rankid".md5($name)."' data-type='artist' data-name='".htmlentities($name, ENT_QUOTES)."' data-mbid='' data-artist='".htmlentities($name, ENT_QUOTES)."'>
			".self::loader(60)."
			</div>
			";
		return "
		<div class='row'>
			".$img."
			<div class='col-xs-9'>
				<a href={$url} target='_blank'>{$name}</a>
				<br>
				<small class='text-muted'>{$playcount} ".(Lang::get('play_x'))."</small>
				<br>
				<div class='progress'>
				  <div class='progress-bar progress-bar-default' role='progressbar' aria-valuenow='{$perc}' aria-valuemin='0' aria-valuemax='100' style='width: {$perc}%'>
				    <span class='sr-only'>{$perc}% of the number 1 act</span>
				  </div>
				</div>
			</div>
		</div>
		";
	}

	static function topAlbListRow($name, $url, $playcount, $img, $biggest, $artist, $arturl, $login)
	{
		$perc = $playcount/$biggest*100;
		$url = Route::url("lib_alb", array("name" => F::fixLFM($name), "artist" => F::fixLFM($artist), "login" => $login));
		$arturl = Route::url("lib_art", array("name" => F::fixLFM($artist), "login" => $login));
		if(empty($img))
		{
			$img = "
			<div class='col-xs-3 getimage imgfix' id='rankid".md5($name)."' data-type='album' data-name='".htmlentities($name, ENT_QUOTES)."' data-mbid='' data-artist='".htmlentities($artist, ENT_QUOTES)."'>
			".self::loader(60)."
			</div>
			";
		}
		else
		{
			$img = "
			<div class='col-xs-3'>
				<img class='img-responsive' src='{$img}' alt='{$name}'>
			</div>";
		}
		return "
		<div class='row'>
			".$img."
			<div class='col-xs-9'>
				<a href={$url} target='_blank'>{$name}</a> 
				<br>
				<small class='text-muted'>".Lang::get('by')." <a href={$arturl} target='_blank'>{$artist}</a></small>
				<br>
				<small class='text-muted'>{$playcount} ".(Lang::get('play_x'))."</small>
				<br>
				<div class='progress'>
				  <div class='progress-bar progress-bar-default' role='progressbar' aria-valuenow='{$perc}' aria-valuemin='0' aria-valuemax='100' style='width: {$perc}%'>
				    <span class='sr-only'>{$perc}% of the number 1 act</span>
				  </div>
				</div>
			</div>
		</div>
		";
	}

	static function topMusListRow($name, $url, $playcount, $img, $biggest, $artist, $arturl, $album, $alburl, $login)
	{
		$perc = $playcount/$biggest*100;
		$url = Route::url("lib_mus", array("name" => F::fixLFM($name), "artist" => F::fixLFM($artist), "login" => $login));
		$arturl = Route::url("lib_art", array("name" => F::fixLFM($artist), "login" => $login));
		$img = "
			<div class='col-xs-3 getimage imgfix' id='rankid".md5($name)."' data-type='music' data-name='".htmlentities($name, ENT_QUOTES)."' data-mbid='' data-artist='".htmlentities($artist, ENT_QUOTES)."'>
			".self::loader(60)."
			</div>
			";
		return "
		<div class='row'>
			".$img."
			<div class='col-xs-9'>
				<a href=".$url." target='_blank'>{$name}</a> 
				<br>
				<small class='text-muted'>".Lang::get('by')." <a href={$arturl} target='_blank'>{$artist}</a></small>
				<br>
				<small class='text-muted'>{$playcount} ".(Lang::get('play_x'))."</small>
				<br>
				<div class='progress'>
				  <div class='progress-bar progress-bar-default' role='progressbar' aria-valuenow='{$perc}' aria-valuemin='0' aria-valuemax='100' style='width: {$perc}%'>
				    <span class='sr-only'>{$perc}% of the number 1 act</span>
				  </div>
				</div>
			</div>
		</div>
		";
	}

	static function loader($size)
	{
		return "
		<img src='".Url::asset("img/loader.gif")."' style='margin: 15px 0px; width: ".$size."px' alt='loading...'>
		";
	}

	static function specMusRow($value, $user, $move, $icon)
	{
		$lfm = new \LastFmApi\Main\LastFm();
		$lfm->setUser($user->login);
		$art = $lfm->getMusicInfo($value->music, $value->artist, $value->mus_mbid);
		$bgimg = $art['album']['image']['extralarge'];
		$html = "<div class='row'>";
		$html .= "<div class='col-xs-12'>";
		if (empty($bgimg)) {
			$html .= "<div style='background: url(".$bgimg.") center; background-size:cover;' data-spotify-artist-bg='".htmlentities($value->artist, ENT_QUOTES)."'>";
		} else {
			$html .= "<div style='background: url(".$bgimg.") center; background-size:cover;'>";
		}
		$html .= "<div class='bg text-center'>";
		$html .= "<div class='row'>";
		$html .= "<div class='col-sm-8 white'>";
		$html .= "<h1>".$value->music."<small class='text-muted-alt br'>".$value->artist."</small><h1>";
		$html .= "<div class='row bigtxt'>";
		$html .= "<div class='col-xs-4'>";
		$html .= "#".$value->rank."<small>".Lang::get('rk')."</small>";
		$html .= "</div>";
		$html .= "<div class='col-xs-4'>";
		$html .= "".$value->playcount."<small>".(Lang::get('play_x'))."</small>";
		$html .= "</div>"; // col-xs-4
		$html .= "<div class='col-xs-4'>";
		$html .= "<i class='".$icon."'></i><small>".$move."</small>";
		$html .= "</div>"; // col-xs-4
		$html .= "</div>"; // row3
		$html .= "</div>"; // col-md-8
		$html .= "<div class='col-sm-4'>";
		if (empty($bgimg)) {
			$html .= "<img class='full' src='/web/img/default-art.png' data-spotify-artist='".htmlentities($value->artist, ENT_QUOTES)."'>";
		} else {
			$html .= "<img class='full' src='".$bgimg."'>";
		}
		$html .= "</div>"; // col-md-4
		$html .= "</div>"; // row2
		$html .= "</div>"; // bg
		$html .= "</div>"; // background
		$html .= "</div>"; // col-xs-12
		$html .= "</div>"; // row1
		
		return $html;
	}

	static function specArtRow($value, $user, $move, $icon)
	{
		$lfm = new \LastFmApi\Main\LastFm();
		$lfm->setUser($user->login);
		$art = $lfm->getArtistInfo($value->artist, $value->art_mbid);
		$html = "<div class='row'>";
		$html .= "<div class='col-xs-12'>";
		$html .= "<div style='background: url(".$art['images']['mega'].") center; background-size:cover;' data-spotify-artist-bg='".htmlentities($value->artist, ENT_QUOTES)."'>";
		$html .= "<div class='bg text-center'>";
		$html .= "<div class='row'>";
		$html .= "<div class='col-sm-8 white'>";
		$html .= "<h1>".$value->artist."<h1>";
		$html .= "<div class='row bigtxt'>";
		$html .= "<div class='col-xs-4'>";
		$html .= "#".$value->rank."<small>".Lang::get('rk')."</small>";
		$html .= "</div>";
		$html .= "<div class='col-xs-4'>";
		$html .= "".$value->playcount."<small>".(Lang::get('play_x'))."</small>";
		$html .= "</div>"; // col-xs-4
		$html .= "<div class='col-xs-4'>";
		$html .= "<i class='".$icon."'></i><small>".$move."</small>";
		$html .= "</div>"; // col-xs-4
		$html .= "</div>"; // row3
		$html .= "</div>"; // col-md-8
		$html .= "<div class='col-sm-4'>";
		$html .= "<img class='full' data-spotify-artist='".htmlentities($value->artist, ENT_QUOTES)."' src='".$art['images']['extralarge']."'>";
		$html .= "</div>"; // col-md-4
		$html .= "</div>"; // row2
		$html .= "</div>"; // bg
		$html .= "</div>"; // background
		$html .= "</div>"; // col-xs-12
		$html .= "</div>"; // row1

		return $html;
	}

	static function specAlbRow($value, $user, $move, $icon)
	{
		$lfm = new \LastFmApi\Main\LastFm();
		$lfm->setUser($user->login);
		$art = $lfm->getAlbumInfo($value->album, $value->artist, $value->alb_mbid);
		$html = "<div class='row'>";
		$html .= "<div class='col-xs-12'>";
		$html .= "<div style='background: url(".$art['images']['mega'].") center; background-size:cover;'>";
		$html .= "<div class='bg text-center'>";
		$html .= "<div class='row'>";
		$html .= "<div class='col-sm-8 white'>";
		$html .= "<h1>".$value->album."<small class='text-muted-alt br'>".$value->artist."</small><h1>";
		$html .= "<div class='row bigtxt'>";
		$html .= "<div class='col-xs-4'>";
		$html .= "#".$value->rank."<small>".Lang::get('rk')."</small>";
		$html .= "</div>";
		$html .= "<div class='col-xs-4'>";
		$html .= "".$value->playcount."<small>".(Lang::get('play_x'))."</small>";
		$html .= "</div>"; // col-xs-4
		$html .= "<div class='col-xs-4'>";
		$html .= "<i class='".$icon."'></i><small>".$move."</small>";
		$html .= "</div>"; // col-xs-4
		$html .= "</div>"; // row3
		$html .= "</div>"; // col-md-8
		$html .= "<div class='col-sm-4'>";
		$html .= "<img class='full' src='".$art['images']['extralarge']."'>";
		$html .= "</div>"; // col-md-4
		$html .= "</div>"; // row2
		$html .= "</div>"; // bg
		$html .= "</div>"; // background
		$html .= "</div>"; // col-xs-12
		$html .= "</div>"; // row1

		return $html;
	}

	static function getMove($show_move, $move, $lw, $ispt = false)
	{
		switch ($show_move) {
		case C::SHOW_MOVE_HIDDEN:
			$move = "";
			break;

		case C::SHOW_MOVE_DIFF:
			$move = ($move <> 0 || !is_numeric($move) ? $move : "=");
			break;

		case C::SHOW_MOVE_LW:
			$move = $lw;
			break;

		case C::SHOW_MOVE_PERC:
			if($ispt)
			{
				if(is_numeric($lw))
				{
					$tw = $lw + intval($move);
					$move = round((($tw/$lw)-1)*100, 2);
					if($move <> 0)
					{
						$move .= "%";
					}
					else
					{
						$move = "=";
					}
				}
				else
				{
					$move = $lw;
				}
			}
			else
			{
				$move = self::getMove(C::SHOW_MOVE_DIFF, $move, $lw);
			}
			break;
		}
		return $move;
	}

	static function getMoveClass($show_move, $move, $tw, $isrank)
	{
		$color = "";
		if($move == "=")
		{
			$color = "non";
		}
		elseif($move == "NEW")
		{
			$color = "deb";
		}
		elseif($move == "RE")
		{
			$color = "ret";
		}
		else
		{
			$move = intval($move);
			if($show_move == C::SHOW_MOVE_DIFF || $show_move == C::SHOW_MOVE_PERC)
			{
				$color = $move > 0 ? "up" : "down";
			}
			elseif($show_move == C::SHOW_MOVE_LW)
			{
				$cond = $isrank ? $tw < $move : $tw > $move;
				$color = $cond ? "up" : "down";
			}
		}

		return $color;
	}

	static function chartRun($type, $cr, $user, $stats, $limit, $name, $artist = false)
	{
		$peak = isset($stats["overall"]["peak"]) ? $stats["overall"]["peak"] : 0;
		$totalweeks = $stats["weeks"]["total"];
		$wkstop1 	= $stats["weeks"]["top01"];
		$wkstop5 	= $stats["weeks"]["top05"];
		$wkstop10 	= $stats["weeks"]["top10"];
		$wkstop20 	= $stats["weeks"]["top20"];
		ob_start();
		include MAIN_DIR.'/view/inc/cr.php';
		$run = ob_get_clean();
		return $run;
	}

	static function friendsButton($type, $id, $title)
	{
		switch ($type) {
			case 'add':
				$btn = "<a data-id='".$id."' class='no-decoration no-margin tipup btn btn-custom-alt btn-success add_friend' title='".$title."' href='#!'><i class='fa fa-fw fa-plus'></i></a>";
				break;

			case 'remove':
				$btn = "<a data-id='".$id."' class='no-decoration no-margin tipup btn btn-custom-alt btn-info remove_friend' title='".$title."' href='#!'><i class='fa fa-fw fa-check'></i></a>";
				break;

			case 'wait':
				$btn = "<a class='no-decoration no-margin tipup btn btn-custom-alt btn-info' title='".$title."' href='#!'><i class='fa fa-fw fa-clock-o'></i></a>";
				break;

			case 'cancel':
				$btn = "<a data-id='".$id."' class='no-decoration no-margin tipup btn btn-custom-alt btn-danger remove_friend' title='".$title."' href='#!'><i class='fa fa-fw fa-times'></i></a>";
				break;
			
			default:
				$btn = "";
				break;
		}

		return $btn;
	}
}
?>