<?php
namespace MRBS;


class Groups extends TableIterator
{

  public function __construct()
  {
    parent::__construct(__NAMESPACE__ . '\\Group');
    $this->names = array();
    $roles = new Roles();
    $this->names['roles'] = $roles->getNames();
  }


  public function next()
  {
    $this->cursor++;

    if (false !== ($row = $this->res->next_row_keyed()))
    {
      $this->item = new $this->base_class();
      $this->item->load($row);
    }
  }


  // Returns an array of group names indexed by id.
  public function getNames()
  {
    $result = array();
    foreach ($this as $group)
    {
      $result[$group->id] = $group->name;
    }
    return $result;
  }


  public static function idsToNames(array $ids)
  {
    static $names;

    if (!isset($names))
    {
      $groups = new self();
      $names = $groups->getNames();
    }

    $result = array();

    foreach ($ids as $id)
    {
      if (isset($names[$id]))
      {
        $result[] = $names[$id];
      }
      else
      {
        trigger_error("Id $id does not exist");
      }
    }

    sort($result, SORT_LOCALE_STRING | SORT_FLAG_CASE);

    return $result;
  }

  protected function getRes($sort_column = null)
  {
    global $auth;

    $class_name = $this->base_class;
    $table_name = _tbl($class_name::TABLE_NAME);
    $sql_params = array(':auth_type' => $auth['type']);
    $sql = "SELECT G.*, " . db()->syntax_group_array_as_string('R.role_id') . " AS roles
              FROM $table_name G
         LEFT JOIN " . _tbl('group_role') . " R
                ON R.group_id=G.id
             WHERE G.auth_type=:auth_type
          GROUP BY G.id
          ORDER BY G.name";
    $this->res = db()->query($sql, $sql_params);
    $this->cursor = -1;
    $this->item = null;
  }
}
