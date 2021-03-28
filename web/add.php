<?php
namespace MRBS;

use MRBS\Form\Form;

require "defaultincludes.inc";
require_once "mrbs_sql.inc";


// Check the CSRF token
Form::checkToken();

// Check the user is authorised for this page
checkAuthorised(this_page());

// Get non-standard form variables
$name = get_form_var('name', 'string', null, INPUT_POST);
$description = get_form_var('description', 'string', null, INPUT_POST);
$capacity = get_form_var('capacity', 'int', null, INPUT_POST);
$room_admin_email = get_form_var('room_admin_email', 'string', null, INPUT_POST);
$type = get_form_var('type', 'string', null, INPUT_POST);

// This file is for adding new areas/rooms
$error = '';

// First of all check that we've got an area or room name
if (!isset($name) || ($name === ''))
{
  $error = "empty_name";
}

// we need to do different things depending on if it's a room
// or an area
elseif ($type === 'area')
{
  $area_object = new Area($name);
  // Lock the table in case somebody else manages to create a table of the
  // same name in between us checking that the name is unique and saving the area
  if (!db()->mutex_lock(_tbl(Area::TABLE_NAME)))
  {
    fatal_error(get_vocab('failed_to_acquire'));
  }
  if ($area_object->exists())
  {
    $error = 'invalid_area_name';
  }
  else
  {
    $area_object->save();
    $area = $area_object->id;
  }
  // Unlock the table
  db()->mutex_unlock(_tbl(Area::TABLE_NAME));
}

elseif ($type === 'room')
{
  $room = mrbsAddRoom($name, $area, $error, $description, $capacity, $room_admin_email);
}

$returl = "admin.php?area=$area" . (!empty($error) ? "&error=$error" : "");
location_header($returl);

