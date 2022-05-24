<?php
    /* μlogger
    *
    * Copyright(C) 2022 Rene Stahmann
    *
    * This is free software; you can redistribute it and/or modify it under
    * the terms of the GNU General Public License as published by
    * the Free Software Foundation; either version 3 of the License, or
    * (at your option) any later version.
    *
    * This program is distributed in the hope that it will be useful, but
    * WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    * General Public License for more details.
    *
    * You should have received a copy of the GNU General Public License
    * along with this program; if not, see <http://www.gnu.org/licenses/>.
    */

    /**
     * Config, add µLogger Credentials and the relative Path to µLogger
     */

    define('USER',                                                                  'UloggerUsername');
    define('PASSWORD',                                                              'UloggerPassword');
    define('ULOGGER_PATH',                                                          'ulogger');

   /**
     * https://arjunphp.com/php-multidimensional-array-searching/
     */
    function recursiveArraySearch($haystack, $needle, $index = null){
        $aIt                                                                        = new RecursiveArrayIterator($haystack);
        $it                                                                         = new RecursiveIteratorIterator($aIt);

        while($it->valid()){
            if(((isset($index) AND ($it->key() == $index)) OR (!isset($index))) AND ($it->current() == $needle)){
                return $aIt->key();
            }
            $it->next();
        }
        return false;
    }

    if(file_exists(ULOGGER_PATH . '/helpers/auth.php')){
        if(include_once(ULOGGER_PATH . '/helpers/auth.php')){
            $auth                                                                   = new uAuth();
            $loggedIn                                                               = $auth->checkLogin(USER, PASSWORD);

            if($loggedIn){
                /**
                 * display all Track IDs
                 */
                if(!isset($_GET['id'])){
                    if(!class_exists('uTrack')){
                        include_once(ULOGGER_PATH . '/helpers/track.php');
                    }
                    $tracks                                                         = (array) uTrack::getAll();

                    if(!empty($tracks)){
                        echo '<ul>';
                        foreach($tracks as $key => $track){
                            $track                                                  = (array) $track;
                            echo '<li><span class="trackid">ID: <code>' . $track['id'] . '</code></span> - <span class="trackname">Name: <code>' . $track['name'] . '</code></span></li>';
                        }
                        echo '</ul>';
                    }                
                } else {
                    /**
                     * Retrieve Data
                     */
                    if(isset($_GET['id'])) {
                        if(isset($_GET['id'])){
                            $trackId                                                = $_GET['id'];
                        } else {
                            $error                                                  = TRUE;
                        }
                        if(isset($_GET['lat'])){
                            $lat                                                    = floatval($_GET['lat']);
                        } else {
                            $error                                                  = TRUE;
                        }
                        if(isset($_GET['lon'])){
                            $lon                                                    = floatval($_GET['lon']);
                        } else {
                            $error                                                  = TRUE;
                        }
                        if(isset($_GET['timestamp'])){
                            $timestamp                                              = $_GET['timestamp'];
                        } else {
                            $error                                                  = TRUE;
                        }

                        if(isset($_GET['altitude'])){
                            $altitude                                               = $_GET['altitude'];
                        }
                        if(isset($_GET['speed'])){
                            $speed                                                  = $_GET['speed'];
                        }
                        if(isset($_GET['bearing'])){
                            $bearing                                                = $_GET['bearing'];
                        }
                        if(isset($_GET['accuracy'])){
                            $accuracy                                               = $_GET['accuracy'];
                        }
                    } elseif(isset($_POST['id'])){
                        if(isset($_POST['id'])){
                            $trackId                                                = $_POST['id'];
                        } else {
                            $error                                                  = TRUE;
                        }
                        if(isset($_POST['lat'])){
                            $lat                                                    = floatval($_POST['lat']);
                        } else {
                            $error                                                  = TRUE;
                        }
                        if(isset($_POST['lon'])){
                            $lon                                                    = floatval($_POST['lon']);
                        } else {
                            $error                                                  = TRUE;
                        }
                        if(isset($_POST['timestamp'])){
                            $timestamp                                              = $_POST['timestamp'];
                        } else {
                            $error                                                  = TRUE;
                        }

                        if(isset($_POST['altitude'])){
                            $altitude                                               = $_POST['altitude'];
                        }
                        if(isset($_POST['speed'])){
                            $speed                                                  = $_POST['speed'];
                        }
                        if(isset($_POST['bearing'])){
                            $bearing                                                = $_POST['bearing'];
                        }
                        if(isset($_POST['accuracy'])){
                            $accuracy                                               = $_POST['accuracy'];
                        }
                    }

                    if(!isset($error)){
                        if(!isset($speed)){
                            $speed                                                  = 0;
                        }
                        if(!isset($bearing)){
                            $bearing                                                = 0;
                        }
                        if(!isset($accuracy)){
                            $accuracy                                               = 0;
                        }
                        if(!isset($altitude)){
                            $altitude                                               = 0;
                        }

                        $lat                                                        = filter_var($lat,                  FILTER_VALIDATE_FLOAT);
                        $lon                                                        = filter_var($lon,                  FILTER_VALIDATE_FLOAT);
                        $altitude                                                   = filter_var($altitude,             FILTER_VALIDATE_FLOAT);
                        $speed                                                      = filter_var($speed,                FILTER_VALIDATE_FLOAT);
                        $bearing                                                    = filter_var($bearing,              FILTER_VALIDATE_FLOAT);
                        $trackId                                                    = filter_var($trackId,              FILTER_VALIDATE_INT);
                        $provider                                                   = trim($provider);
                        $imageMeta                                                  = '';
                        $image                                                      = '';
                        $comment                                                    = '';

                        if (empty($lat) || empty($lon) || empty($timestamp) || empty($trackId)) {
                            print_r("Missing required parameter");
                        } else {
                            if(!class_exists('uPosition')){
                                require_once(ULOGGER_PATH . "/helpers/position.php");
                            }
                            $tracks                                                 = (array) uTrack::getAll();
                            $id_key                                                 = recursiveArraySearch($tracks, $trackId, 'id');

                            if(!is_numeric($id_key)){
                                $trackName                                          = 'Auto_ID_' . $trackId . '_' . date('Y_m_d');
                                $trackName_key                                      = recursiveArraySearch($tracks, $trackName, 'name');

                                if(is_numeric($trackName_key)){
                                    $tracks[$trackName_key]                         = (array) $tracks[$trackName_key];
                                    $trackId                                        = $tracks[$trackName_key]['id'];
                                } else {
                                    /**
                                     * New Track is needed
                                     */
                                    if(!class_exists('uTrack')){
                                        require_once(ULOGGER_PATH . "/helpers/track.php");
                                    }

                                    $NewTrackId                                     = uTrack::add($auth->user->id, $trackName);

                                    if ($NewTrackId === false) {
                                        exitWithError("Server error NewTrackID");
                                    } else {
                                        $trackId                                    = $NewTrackId;
                                    }
                                }
                            }
                            $positionId                                             = uPosition::add($auth->user->id, $trackId, $timestamp, $lat, $lon, $altitude, $speed, $bearing, $accuracy, $provider, $comment, $image);

                            if ($positionId === false) {
                                print_r("Server error PosID");
                            } else {
                                $response                                           = [];
                                $params                                             = [];
                                $response['error']                                  = false;
                                header('Content-Type: application/json');
                                echo json_encode(array_merge($response, $params));
                            }
                        }
                    } else {
                        print_r('Something went wrong.<br>Please check the Client Parameters.');
                    }
                }
            } else {
                print_r('Hm ... your credentials are wrong. Please check again.');
            }
        } else {
            print_r('Seems like a permission Problem.');
        }
    } else {
        print_r('Please check the Config Section and fix the ULOGGER_PATH.');
    }
?>
