<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Mapper;

use PDO;
use PDOException;
use uLogger\Entity;
use uLogger\Exception\DatabaseException;
use uLogger\Exception\ServerException;

class Config extends AbstractMapper {

  /**
   * Read config values from database
   * @return Entity\Config
   * @throws DatabaseException
   * @throws ServerException
   */
  public function fetch(): Entity\Config {
    try {
      $query = 'SELECT name, value FROM ' . $this->db->table('config');
      $result = $this->db->query($query);
      $configArray = $result->fetchAll(PDO::FETCH_KEY_PAIR);
      $config = $this->mapRowToObject($configArray);
      $this->setLayersFromDatabase($config);
      return $config;

    } catch (PDOException $e) {
      syslog(LOG_ERR, $e->getMessage());
      throw new DatabaseException($e->getMessage());
    }
  }

  /**
   * Save config values to database
   * @throws DatabaseException
   */
  public function update(Entity\Config $config): void {
    // PDO::PARAM_LOB doesn't work here with pgsql, why?
    $placeholder = $this->db->lobPlaceholder();
    $values = [
      [ "'color_extra'", $placeholder ],
      [ "'color_hilite'", $placeholder ],
      [ "'color_normal'", $placeholder ],
      [ "'color_start'", $placeholder ],
      [ "'color_stop'", $placeholder ],
      [ "'google_key'", $placeholder ],
      [ "'latitude'", $placeholder ],
      [ "'longitude'", $placeholder ],
      [ "'interval_seconds'", $placeholder ],
      [ "'lang'", $placeholder ],
      [ "'map_api'", $placeholder ],
      [ "'pass_lenmin'", $placeholder ],
      [ "'pass_strength'", $placeholder ],
      [ "'public_tracks'", $placeholder ],
      [ "'require_auth'", $placeholder ],
      [ "'stroke_color'", $placeholder ],
      [ "'stroke_opacity'", $placeholder ],
      [ "'stroke_weight'", $placeholder ],
      [ "'units'", $placeholder ],
      [ "'upload_maxsize'", $placeholder ]
    ];

    $params = [
      $config->colorExtra,
      $config->colorHilite,
      $config->colorNormal,
      $config->colorStart,
      $config->colorStop,
      $config->googleKey,
      $config->initLatitude,
      $config->initLongitude,
      $config->interval,
      $config->lang,
      $config->mapApi,
      $config->passLenMin,
      $config->passStrength,
      $config->publicTracks,
      $config->requireAuthentication,
      $config->strokeColor,
      $config->strokeOpacity,
      $config->strokeWeight,
      $config->units,
      $config->uploadMaxSize
    ];

    try {
      $query = $this->db->insertOrReplace('config', [ 'name', 'value' ], $values, 'name', 'value');
      $stmt = $this->db->prepare($query);
      $stmt->execute(array_map('serialize', $params));
      $this->saveLayers($config);
    } catch (PDOException $e) {
      syslog(LOG_ERR, $e->getMessage());
      throw new DatabaseException($e->getMessage());
    }
  }

  /**
   * Truncate ol_layers table
   * @throws PDOException
   */
  private function deleteLayers(): void {
    $query = 'DELETE FROM ' . $this->db->table('ol_layers');
    $this->db->exec($query);
  }

  /**
   * Save layers to database
   * @throws PDOException
   */
  private function saveLayers(Entity\Config $config): void {
    $this->deleteLayers();
    if (!empty($config->olLayers)) {
      $query = 'INSERT INTO ' . $this->db->table('ol_layers') . ' (id, name, url, priority) VALUES (?, ?, ?, ?)';
      $stmt = $this->db->prepare($query);
      foreach ($config->olLayers as $layer) {
        $stmt->execute([ $layer->id, $layer->name, $layer->url, $layer->priority ]);
      }
    }
  }

  /**
   * Read config values from database
   * @throws PDOException
   */
  private function setLayersFromDatabase(Entity\Config $config): void {
    $config->olLayers = [];
    $query = 'SELECT id, name, url, priority FROM ' . $this->db->table('ol_layers');
    $result = $this->db->query($query);
    while ($row = $result->fetch()) {
      $config->olLayers[] = new Entity\Layer((int) $row['id'], $row['name'], $row['url'], (int) $row['priority']);
    }
  }

  /**
   * Unserialize data from database
   * @param resource|string $data Resource returned by pgsql, string otherwise
   * @return mixed
   */
  private function unserialize(mixed $data): mixed {
    if (is_resource($data)) {
      $data = stream_get_contents($data);
    }
    return unserialize($data, [ 'allowed_classes' => false ]);
  }

  /**
   * @throws ServerException
   */
  private function mapRowToObject(array $row): Entity\Config {
    $row = array_map([ $this, 'unserialize' ], $row);
    return Entity\Config::fromDatabaseRow($row);
  }

}


