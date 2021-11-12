<?php

namespace Zbxin\Events;

use Zbxin\Contracts\Listener;

/**
 * Trait ListensMapping
 * @package Zbxin\Events
 */

trait ListensMapping
{
  /**
   * @param $eventPath
   * @param $listenerPath
   * @return array
   */

  protected function loadPathEventMapping($eventPath, $listenerPath)
  {
    $eventNameSpacePath = str_replace('.', '\\', $eventPath);
    $eventPath = str_replace('.', DIRECTORY_SEPARATOR, $eventPath);
    $listenerNameSpacePath = str_replace('.', '\\', $listenerPath);
    $listenerPath = str_replace('.', DIRECTORY_SEPARATOR, $listenerPath);
    $eventBasePath = app_path($eventPath);
    $pathList = array_merge([$eventBasePath], $this->getPathDirList($eventBasePath));
    $mapping = [];
    $eventNamespace = 'App\\' . $eventNameSpacePath . '\\';
    $listenerNamespace = 'App\\' . $listenerNameSpacePath . '\\';
    $listenerBasePath = app_path(str_replace('.', DIRECTORY_SEPARATOR, $listenerPath));
    foreach ($pathList as $basePath) {
      foreach (glob($basePath . '/*.php') as $event) {
        $eventName = substr(str_replace($basePath, '', $event), 1, -4);
        $group = substr($basePath, strrpos($basePath, $eventPath) + strlen($eventPath) + 1);
        $eventSpace = '';
        if (!empty($group)) {
          $eventSpace = $group . '\\' . $eventName;
          $eventName = $group . DIRECTORY_SEPARATOR . $eventName;
        }
        $eventListenerPath = $listenerBasePath . DIRECTORY_SEPARATOR . $eventName;
        $listeners = [];
        foreach (glob($eventListenerPath . '/*.php') as $listener) {
          $listenerName = substr(str_replace($eventListenerPath, '', $listener), 1, -4);
          /**
           * @var Listener $listenerModel
           */
          $listenerModel = $listenerNamespace . $eventSpace . '\\' . $listenerName;
          method_exists($listenerModel, 'getListenOrder') && $listeners[$listenerModel::getListenOrder()] = $listenerModel;
        }
        ksort($listeners);
        !empty($listeners) && $mapping[$eventNamespace . $eventSpace] = array_values($listeners);
      }
    }
    return $mapping;
  }

  /**
   * @param $path
   * @return array
   */

  protected function getPathDirList($path)
  {
    $dirList = [];
    $pathFiles = dir($path);
    while ($file = $pathFiles->read()) {
      if ($file === '.') {
        continue;
      }
      if ($file === '..') {
        continue;
      }
      $fullPath = $path . DIRECTORY_SEPARATOR . $file;
      if (is_dir($fullPath)) {
        $dirList[] = $fullPath;
      }
    }
    return $dirList;
  }
}
