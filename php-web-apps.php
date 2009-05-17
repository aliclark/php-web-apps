<?php

//// Copyright (c) 2009 Ali Clark
//// 
//// Permission is hereby granted, free of charge, to any person
//// obtaining a copy of this software and associated documentation
//// files (the "Software"), to deal in the Software without
//// restriction, including without limitation the rights to use,
//// copy, modify, merge, publish, distribute, sublicense, and/or sell
//// copies of the Software, and to permit persons to whom the
//// Software is furnished to do so, subject to the following
//// conditions:
//// 
//// The above copyright notice and this permission notice shall be
//// included in all copies or substantial portions of the Software.
//// 
//// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
//// EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
//// OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
//// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
//// HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
//// WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
//// FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
//// OTHER DEALINGS IN THE SOFTWARE.
//// 
//// Except as contained in this notice, the name(s) of the above
//// copyright holders shall not be used in advertising or otherwise
//// to promote the sale, use or other dealings in this Software
//// without prior written authorization.

//// This file provides definitions for a bunch of functions which are
//// almost guaranteed to be useful for any web app you make.
//// You can mix and match, as they are quite general, but if you use
//// the whole enchalada as intended, you can have a web app in very few lines,
//// perhaps a small / medium sized app for 500 lines total, including html.

//// These functions are written in a highly functional style.
//// This means that you can be fairly happy about the stability of the code.
//// Another upshot (amongst many) of this, is that you can also sleep quite
//// soundly years down the line when PHP starts declining. Whereas other
//// programming styles translate less easily to other languages, just about
//// all of this code can be trivially ported to another language when
//// the iminent decline of PHP arrives.

//// The largest function definitions in this file are about 10 lines of code,
//// and most functions are just a couple of lines. This makes it incredibly
//// easy to know what is going on at every stage and change if appropriate.

//// Type hints and exceptions are also used where applicable,
//// as these are also sound programming techniques for building reliable code.

//// Slowly, slowly, catch a monkey.

define( 'ERROR_GET_VARIABLE', 'note');

$field_to_input_callbacks = array(
  'hidden'    => 'hidden_field_to_input',
  'enum'      => 'enum_field_to_input',
  'radioenum' => 'radioenum_field_to_input',
  'id'        => 'id_field_to_input',
  'time'      => 'time_field_to_input',
  'regular'   => 'regular_field_to_input'
);

/// mixed

function identity (&$x) {
  return $x;
}

function value_or_default (array $data, $name, $default) {
  return (array_key_exists( $name, $data) ? $data[$name] : $default);
}

function x_or_null (array $data, $x) {
  return value_or_default( $data, $x, null);
}

function first_or_null (array $data) {
  return x_or_null( $data, 0);
}

function x_of_first (array $data, $x) {
  return $data[0][$x];
}

function find ($predicate, array $data) {
  foreach ($data as $item) {
    if ($predicate( $item)) {
      return $item;
    }
  }
  return null;
}

function attribute_clean ($value) {
  return (is_string( $value) ? htmlspecialchars( $value, ENT_QUOTES) : $value);
}

function html_clean ($value) {
  return (is_string( $value) ? nl2br( attribute_clean( $value)) : $value);
}

function no_magic_quotes ($value) {
  return ((is_string( $value) && get_magic_quotes_gpc()) ?
    stripslashes( $value) :
    $value);
}

function database_clean ($value, $connection) {
  return (is_string( $value) ?
    mysql_real_escape_string( no_magic_quotes( $value), $connection) :
    $value);
}

/// bool

function not_null ($a) {
  return $a !== null;
}

function string_empty ($value) {
  return ($value === '');
}

function string_not_empty ($value) {
  return !string_empty( $value);
}

function array_empty (array $data) {
  return (count( $data) == 0);
}

function value_is_default (array $data, $name, $default) {
  return (value_or_default( $data, $name, $default) === $default);
}

function any_partial_right ($predicate, array $data, array $rest) {
  foreach ($data as $item) {
    if (call_user_func_array(
          $predicate,
          array_merge( array( $item), $rest))) {
      return true;
    }
  }
  return false;
}

/// number

function decrement ($number) {
  return ($number - 1);
}

function base2 ($number) {
  return pow( 2, $number);
}

function count_uniques (array $data) {
  return count( array_unique( $data));
}

/// string

function mergeplode (array $strings) {
  return implode( '', $strings);
}

function spaceplode (array $strings) {
  return implode( ' ', $strings);
}

function complode (array $strings) {
  return implode( ',', $strings);
}

function current_datetime () {
  return date( 'Y-m-d H:i:s');
}

function zeropad ($n, $val) {
  return str_pad($val, $n, 0, STR_PAD_LEFT);
}

function array_mapmerge ($callback, array $data) {
  return mergeplode( array_map( $callback, $data));
}

/// array

function constant_array () {
  return array();
}

function take ($n, array $data) {
  $rv = array();
  for ($i = 0, $len = min( $n, count( $data)); $i < $len; ++$i) {
    $rv[] = $data[$i];
  }
  return $rv;
}

function array_map_partial_right_from (array $rv,
                                       $cback,
                                       array $data,
                                       array $rest) {
  foreach ($data as $item) {
    $rv[] = call_user_func_array(
      $cback,
      array_merge( array( $item), $rest));
  }
  return $rv;
}

function array_map_partial_right ($callback, array $data, array $rest) {
  return array_map_partial_right_from( array(), $callback, $data, $rest);
}

function define_enums (array $names) {
  return array_map( 'define', $names, range( 0, decrement( count( $names))));
}

/// Hash maps

function retrieve_these (array $data, array $keys) {
  $rv = array();
  foreach ($keys as $key) {
    $rv[$key] = $data[$key];
  }
  return $rv;
}

function mask_array (array $names) {
  $rv = array();
  $number = 1;
  foreach ($names as $name) {
    $rv[$name] = base2( $number);
    ++$number;
  }
  return $rv;
}

/// Database connection

class ConnectFailed extends Exception { }
class DBSelectFailed extends Exception { }

function connect_failed () {
  throw new ConnectFailed( 'Connection failed', E_USER_ERROR);
  return null;
}

function db_select_failed () {
  throw new DBSelectFailed( 'Database selection failed', E_USER_ERROR);
  return null;
}

function db_ensure_select ($database, $connection) {
  return (mysql_select_db( $database, $connection) ?
    $connection :
    db_select_failed());
}

function db_complete_connection ($connection, $database) {
  return (is_resource( $connection) ?
    db_ensure_select( $database, $connection) :
    connect_failed());
}

function db_connect ($server, $user, $password, $database) {
  return db_complete_connection(
    mysql_connect( $server, $user, $password),
    $database);
}

/// Data insertion

function db_query_insert_data ($query, array $data, $connection) {
  return mysql_query(
    vsprintf(
      $query,
      array_map_partial_right( 'database_clean', $data, array( $connection))),
    $connection);
}

function query_insert_data ($query, array $data, $connection, $database) {
  return db_query_insert_data(
    $query,
    $data,
    db_ensure_select( $database, $connection));
}

function insert_input_post ($tablename, array $posting, array $fields,
                            $connection,
                            $database) {
  $nonidfields = array_filter( $fields, 'is_not_idfield');
  $keys = field_keys( $nonidfields);
  query_insert_data( "INSERT INTO ".$tablename." ".
    "(".complode( $keys).") ".
    "VALUES (".complode( array_map( 'field_to_printf', $nonidfields)).")",
    retrieve_these( $posting, $keys),
    $connection,
    $database);

  return mysql_insert_id();
}

function update_input_post ($tablename, array $posting, array $fields,
                            $connection,
                            $database) {
  $id = (int) $posting['id'];
  $nonidfields = array_filter( $fields, 'is_not_idfield');
  $keys = field_keys( $nonidfields);
  $idfield = idfield( $fields);

  query_insert_data( "UPDATE ".$tablename." SET ".
    complode( (array_map( 'field_to_update_printf', $nonidfields)))
    ." WHERE ".$idfield['name']."=".$id,
    retrieve_these( $posting, $keys),
    $connection,
    $database);

  return $id;
}

/// Data list getters

class QueryFailed extends Exception { }

function query_failed () {
  throw new QueryFailed( 'Query failed', E_USER_ERROR);
  return null;
}

function ensure_is_resource ($result) {
  return (is_resource( $result) ?
    $result :
    query_failed());
}

function retrieve_query_data ($result, array $rows, $row) {
  while ($row = mysql_fetch_assoc( ensure_is_resource( $result))) {
    $rows[] = $row;
  }
  mysql_free_result( $result);
  return $rows;
}

function query_data ($query, $connection, $database) {
  return retrieve_query_data(
    mysql_query( $query, db_ensure_select( $database, $connection)),
    array(),
    null);
}

// This is a bit inefficient, since it iterates twice, once to retrieve the
// rows, and again to do the map. Feel free to make your own by modifying
// code from query_data if it causes a bottle-neck. Otherwise, leave it :)
function query_data_map ($query, $enumerator, $connection, $database) {
  return array_map( $enumerator, query_data( $query, $connection, $database));
}

/// Data lists to HTML

function elements_list_to_html (array $data, $start, $mapper, $end, $default) {
  return (array_empty( $data) ?
    $default :
    $start.mergeplode(
      is_array( $mapper) ?
        array_map_partial_right( $mapper[0], $data, array_slice( $mapper, 1)):
        array_map( $mapper, $data)
      ).$end);
}

/// HTML cleaning

function insert_html_data ($html, array $data) {
  return vsprintf( $html, array_map( 'html_clean', $data));
}

function insert_attribute_data ($html, array $data) {
  return vsprintf( $html, array_map( 'attribute_clean', $data));
}

/// HTML snippet generation

function enum_to_html ($enum, $name) {
  $val   = is_array( $enum) ? $enum['value'] : $enum;
  $label = is_array( $enum) ? $enum['label'] : $enum;
  $selected = (value_or_default( $_GET, $name, '') == $val) ?
    ' selected="selected"' :
    '';
  return '<option value="'.attribute_clean( $val).'"'.$selected.'>'.
    html_clean( $label).
    '</option>';
}

function enum_to_radio_html ($enum, $name) {
  $val   = attribute_clean( is_array( $enum) ? $enum['value'] : $enum);
  $label = is_array( $enum) ? $enum['label'] : $enum;
  $selected = (value_or_default( $enum, 'default', false) || (value_or_default( $_GET, $name, '') == $val)) ?
    ' checked="checked"' :
    '';
  return '<label for="'.$val.'">'.html_clean( $label).
    '</label> <input type="radio" name="'.$name.'" id="'.$val.
    '" value="'.$val.'"'.$selected.'>';
}

function num_options ($start, $end, $value) {
  $rv  = '';
  $num = null;
  for ($i = $start; $i <= $end; ++$i) {
    $num = zeropad( 2, $i);
    $rv .= '<option value="'.$num.'"'.
      ($value === $num ? ' selected="selected"' : '').'>'.$num.'</option>';
  }
  return $rv;
}

/// HTML input element generation

function html_radio_select_safe ($id, array $enum) {
  $idname = attribute_clean( $id);
  return spaceplode(
    array_map_partial_right( 'enum_to_radio_html', $enum, array( $idname)));
}

function html_input_select_safe ($id, array $enum, $nullable) {
  $idname = attribute_clean( $id);
  return sprintf(
    '<select name="%s" id="%s">%s%s</select>',
    $idname,
    $idname,
    ($nullable ? '<option value=""></option>' : ''),
    mergeplode(
      array_map_partial_right( 'enum_to_html', $enum, array( $idname))));
}

function time_select ($id, $metric, $limit) {
  $idname = attribute_clean( $id);
  return '<select name="'.$idname.'[\''.$metric.'\']">'.
    num_options( 0, $limit,
      value_or_default(
        value_or_default( $_GET, $idname, array()), $metric, '')).
    '</select>';
}

function hour_select ($id) {
  return time_select( $id, 'hour', 23);
}

function minute_select ($id) {
  return time_select( $id, 'minute', 59);
}

function second_select ($id) {
  return time_select( $id, 'second', 59);
}

/// HTML input row generation

function html_input_row_unsafe ($id, $label, $value, $type, $error) {
  return sprintf(
    '<tr><th scope="row"><label for="%s">%s</label></th>'.
    '<td><input name="%s" id="%s" type="%s" value="%s"></td>'.
    '<td>%s</td></tr>',
    $id,
    $label,
    $id,
    $id,
    $type,
    $value,
    $error);
}

function html_input_row_safe ($id, $label, $value, $type, $error) {
  return html_input_row_unsafe(
    attribute_clean( $id),
    html_clean( $label),
    attribute_clean( $value),
    attribute_clean( $type),
    html_clean( $error));
}

function html_hidden_row_unsafe ($id, $value) {
  return sprintf(
    '<tr style="display: none;"><td>&#160;</td><td><input name="%s" id="%s" type="hidden" value="%s"></td><td></td></tr>',
    $id,
    $id,
    $value);
}

function html_hidden_row_safe ($id) {
  return html_hidden_row_unsafe(
    attribute_clean( $id),
    attribute_clean( value_or_default( $_GET, $id, '')));
}

function html_input_select_row_safe ($id, $label, array $enum, $nullable, $error) {
  $idname = attribute_clean( $id);
  return sprintf(
    '<tr><th scope="row"><label for="%s">%s</label></th><td>%s</td><td>%s</td></tr>',
    $idname,
    html_clean( $label),
    html_input_select_safe( $id, $enum, $nullable),
    html_clean( $error));
}

function html_input_radio_row_safe ($id, $label, array $enum, $error) {
  return sprintf(
    '<tr><th scope="row">%s</th><td>%s</td><td>%s</td></tr>',
    html_clean( $label),
    html_radio_select_safe( $id, $enum),
    html_clean( $error));
}

function html_input_time_row ($id, $label) {
  return sprintf(
    '<tr><th scope="row">%s</th><td>%s</td><td>%s</td></tr>',
    html_clean( $label),
    hour_select( $id).minute_select( $id).second_select( $id),
    html_clean( $error));
}

/// Field predicates

function field_nullable (array $field) {
  return value_or_default( $field, 'nullable', false);
}

function text_and_not_nullable (array $field) {
  $type = value_or_default( $field, 'type', '');
  return ((($type == 'text') || ($type == 'password')) &&
    !field_nullable( $field));
}

function is_idfield (array $field) {
  return (value_or_default( $field, 'type', '') === 'id');
}

function is_not_idfield (array $field) {
  return !is_idfield( $field);
}

/// Field strings

function field_to_printf (array $field) {
  return (value_or_default( $field, 'type', '') === 'int') ? '%d' : "'%s'";
}

function field_to_update_printf (array $field) {
  return $field['name'].'='.field_to_printf( $field);
}

function field_to_key (array $field) {
  return $field['name'];
}

function field_label_text (array $field) {
  return $field['label'].(field_nullable( $field) ? ' (optional)' : '');
}

/// Fields

function field_keys (array $fields) {
  return array_map( 'field_to_key', $fields);
}

function idfield (array $fields) {
  return find( 'is_idfield', $fields);
}

/// Field to HTML input row

function hidden_field_to_input (array $field) {
  return html_hidden_row_safe( field_to_key( $field));
}

function enum_field_to_input (array $field) {
  $id = field_to_key( $field);
  return html_input_select_row_safe(
    $id,
    field_label_text( $field),
    $field['enum'],
    value_or_default( $field, 'nullable', false),
    retry_error_text( $id));
}

function radioenum_field_to_input (array $field) {
  $id = field_to_key( $field);
  return html_input_radio_row_safe(
    $id,
    field_label_text( $field),
    $field['enum'],
    retry_error_text( $id));
}

function id_field_to_input (array $field) {
  $id = field_to_key( $field);
  return value_is_default( $_GET, $id, '') ?
    '' :
    html_hidden_row_safe(  $id);
}

function time_field_to_input (array $field) {
  $id = field_to_key( $field);
  return html_input_time_row( $id,
    field_label_text( $field),
    retry_error_text( $id));
}

function regular_field_to_input (array $field) {
  $id = field_to_key( $field);
  return html_input_row_safe(
    $id,
    field_label_text( $field),
    value_or_default( $_GET, $id, ''),
    (value_or_default( $field, 'type', '') === 'password') ?
      'password' :
      'text',
    retry_error_text( $id));
}

function field_to_input (array $field) {
  global $field_to_input_callbacks;
  $intype = value_or_default( $field, 'type', 'regular');
  return array_key_exists( $intype, $field_to_input_callbacks) ?
    $field_to_input_callbacks[$intype]( $field) :
    $field_to_input_callbacks['regular']( $field);
}

/// Form generation

function make_form ($action, array $fields, $submit_name) {
  return '<form action="'.$action.'" method="post">'.
    '<table><tbody>'.
    mergeplode( array_map( 'field_to_input', $fields)).
    '<tr><td></td><td><br>'.
    '<input type="submit" name="'.$submit_name.'" value="Submit">'.
    '</td><td></td></tr></tbody></table></form>';
}

/// GET data retrieval

function add_from_get (array $data, $name) {
  $data[$name] = value_or_default( $_GET, $name, '');
  return $data;
}

function values_from_get (array $fields) {
  $rv = array();
  foreach ($fields as $field) {
    $rv[$field] = value_or_default( $_GET, $field, '');
  }
  return $rv;
}

// This might change somewhat with using error ids.
function get_post_errors () {
  return value_or_default( $_GET, ERROR_GET_VARIABLE, array());
}

/// POST data retrieval

function post_key_exists ($name) {
  return array_key_exists( $name, $_POST);
}

function add_from_post (array $data, $name) {
  $data[$name] = value_or_default( $_POST, $name, '');
  return $data;
}

function retrieve_from_post (array $fields) {
  $rv = array();
  foreach (array_filter( $fields, 'post_key_exists') as $field) {
    $rv[$field] = value_or_default( $_POST, $field, '');
  }
  return $rv;
}

function retrieve_fields_post (array $fields) {
  return retrieve_from_post( field_keys( $fields));
}

/// Form content validation

function validate_input_text ($value) {
  return (is_string( $value) ?
    (($value == '') ?
      'Please enter a value for this field.' :
      '') :
    'Unknown value.');
}

function validate_input_text_paired ($name, array $data) {
  return array( $name, validate_input_text( $data[$name]));
}

function pair_input_text_not_good (array $pair) {
  return ($pair[1] != '');
}

function validate_input_texts (array $fields, array $data) {
  return array_filter(
    array_map_partial_right(
      'validate_input_text_paired',
      $fields,
      array( $data)),
    'pair_input_text_not_good');
}

function basic_form_validation (array $fields, array $data) {
  return validate_input_texts(
    array_map( 'field_to_key',
      array_filter( $fields, 'text_and_not_nullable')),
    $data);
}

/// GET query generation

function post_data_retry_query (array $posting) {
  $rv = array();
  foreach ($posting as $name => $val) {
    $rv[] = urlencode( $name).'='.urlencode( $val);
  }
  return implode( '&', $rv);
}

function post_error_retry_query (array $report) {
  $rv = array();
  foreach ($report as $err) {
    $rv[] = ERROR_GET_VARIABLE.'['.urlencode( $err[0]).'][]='.
      urlencode( $err[1]);
  }
  return implode( '&', $rv);
}

function post_get_retry_query (array $posting, array $report) {
  $data   = post_data_retry_query( $posting);
  $errors = post_error_retry_query( $report);
  return $data.((($data == '') || ($errors == '')) ? '' : '&').$errors;
}

function retry_error_text ($id) {
  return spaceplode(
    value_or_default(
      value_or_default( $_GET, ERROR_GET_VARIABLE, array()), $id, array()));
}

function all_error_texts () {
  return implode( '<br>',
    array_map( 'retry_error_text',
      array_keys( value_or_default( $_GET, ERROR_GET_VARIABLE, array()))));
}

/// Post redirection

function redirect_to ($address) {
  header( 'Location: '.$address);
  exit( 0);
  return null;
}

/// Form predicates

function form_post_key_exists (array $form) {
  return post_key_exists( form_name( $form));
}

/// Form selectors

function form_name (array $form) {
  return $form['name'];
}

function current_form_action (array $forms) {
  return find( 'form_post_key_exists', $forms);
}

/// Form action - These are all onion layers on the same function.

function validation_form_action (array $form, array $posting, array $report) {
  return (array_empty( $report) ?
    $form['success']( $posting) :
    $form['fail']( $posting, $report));
}

function posting_form_action (array $form, array $posting) {
  return validation_form_action(
    $form,
    $posting,
    $form['validator']( $posting));
}

function form_data_action ($form) {
  return (is_null( $form) ?
    null :
    posting_form_action( $form, $form['posting']()));
}

function general_form_data_action (array $forms) {
  return form_data_action( current_form_action( $forms));
}

/// HTML email

function html_mail ($to, $from, $subject, $message) {
  return mail( $to, $subject, $message,
    'MIME-Version: 1.0'."\r\n".
    'Content-type: text/html; charset=iso-8859-1'."\r\n".
    'To: '.$to."\r\n".
    'From: '.$from."\r\n");
}

?>
