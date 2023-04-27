<html lang='ru'>
  <table>
    <tr>
      <td>
          <a href='index.php'>Все товары</a>
      </td>
      <td rowspan="2" bgcolor="#FBF0DB" >
      <?php
        display_all_products();
      ?>
      </td>
    </tr>
    <tr>
      <td valign="top">
        <?php
         display_groups();
        ?>
      </td>      
    </tr>
  </table>
</html>


<?php

function display_groups() {

  if (isset($_GET['id'])) 
  {
    //Если есть, отобразить подгруппы
    $id = $_GET['id'];

    $amount_begin_elements = get_amount_begin_elements_groups();
    if ($id > $amount_begin_elements)
      {
        $groups = get_up_older_parent_groups($id);

        foreach ($groups as $row)
        {
          $older_sibling= get_up_sibling_older($row['id']);

            echo "<ul>";

          if(isset($older_sibling))
          {
            foreach ($older_sibling as $row_sibling)
            {
              echo "<li><a href='index.php?id=" . $row_sibling['id'] . "'>       ". $row_sibling['name'] . "</a>  " . get_count($row_sibling['id']);
            }
          }
          echo "<li> <a href='index.php?id=" . $row['id'] . "'>       ". $row['name'] . "</a>  ". get_count($row['id']);
        }
      }
    
    echo "<ul>";
    
    // $sibling_parent = sibling_parent();
    $groups = get_up_sibling_parent($id);
    if(isset($groups) and $id > $amount_begin_elements)
    {
      foreach ($groups as $row)
      {
        echo "<li margin-bottom='0'><a href='index.php?id=" . $row['id'] . "'>       ". $row['name'] . "</a>  " . get_count($row['id']);
      }
    }

    $groups = get_parent_groups($id);
    foreach ($groups as $row)
    {
      echo "<li margin-bottom='0'><a href='index.php?id=" . $row['id'] . "'>       ". $row['name'] . "</a> ". get_count($row['id']);
    }

    echo "<ul>";

    $groups = get_child_groups($id);

      foreach ($groups as $row)
      {
        echo "<li><a href='index.php?id=" . $row['id'] . "'>" . $row['name'] . "</a>  " . get_count($row['id']);
      }
      echo "</ul>";


    $groups = get_down_sibling_parent($id);

    if(isset($groups ))
    {
      foreach ($groups as $row)
      {
        echo "<li margin-bottom='0'><a href='index.php?id=" . $row['id'] . "'>       ". $row['name'] . "</a>  " . get_count($row['id']);
      }
    echo "</ul>";
    }

    if ($id > $amount_begin_elements)
    {
      $groups = get_up_older_parent_groups($id);

      foreach (array_reverse($groups) as $row)
      {
        $younger_sibling= get_down_sibling_older($row['id']);
        if(isset($younger_sibling))
        {
          foreach ($younger_sibling as $row_sibling)
          {
            // echo "<ul>";
            echo "<li><a href='index.php?id=" . $row_sibling['id'] . "'>       ". $row_sibling['name'] . "</a>  " . get_count($row_sibling['id']);
          }
        }
        echo "</ul>";
      }
    }


  } 
  else 
  {
    //Иначе отобразить список основных групп
    $groups = get_top_level_groups();
    // echo "<ul>";
    foreach ($groups as $row)
    {
      echo "<li><a href='index.php?id=" . $row['id'] . "'>" . $row['name'] . "</a>  ". get_count($row['id'])."</li>";
    }
  }
}



function get_amount_begin_elements_groups() 
{

  $id = 0; 
  $db = new mysqli('localhost', 'root', '', 'z1');
  $result = mysqli_query($db, "SELECT * FROM `groups`");
  $db->close();
  
  $groups = array();
  while ($row = mysqli_fetch_assoc($result)) 
  {
    $groups[] = $row;
  }

  foreach ($groups as $groups_row)
  {

      if ($groups_row['id_parent'] ==  '0' and $groups_row['id'] > $id )
      {
        $id = $groups_row['id'];
      }
  }

  return $id ;
}

//Получение всех родителей, прародителй и т.д., кроме первого элемента группы
function get_up_older_parent_groups($id) 
{
  $id = $_GET['id'];
  $db = new mysqli('localhost', 'root', '', 'z1');
  $result = mysqli_query($db, "SELECT * FROM `groups`");
  $db->close();
  
  while ($row = mysqli_fetch_assoc($result)) 
  {
    $groups[] = $row;
  }
  $groups_up = array();


//начало проверки с возможным входом в рекурсию

  $groups_up = recursion_older_parent_groups($id,  $groups_up, $groups);

  return array_reverse($groups_up);
  
}

function recursion_older_parent_groups($id, $groups_up, $groups) 
{
  $id_parent_element_id = '0';

  foreach ($groups as $groups_row)
  {

    //поиск id_parent у id
      if ($groups_row['id'] == $id)
      {
        $id_parent_element_id = $groups_row['id_parent'];
      }
  }

  //добавление в массив элемента id_parent
  foreach ($groups as $groups_row)
  {                                                                                                                                                                       
      if ($groups_row['id'] == $id_parent_element_id)
      {
        $groups_up[] = $groups_row;
      }
  }

  

  if($id_parent_element_id > 0)
  {
    $groups_up = recursion_older_parent_groups($id_parent_element_id, $groups_up, $groups);
  }
  return $groups_up;
}

function get_up_sibling_older($id) 
{
  $db = new mysqli('localhost', 'root', '', 'z1');
  $result = mysqli_query($db, "SELECT * FROM `groups`");
  $db->close();
  
  $groups = array();
  $id_parent_element_id = '0';

  while ($row = mysqli_fetch_assoc($result)) 
  {
    $groups[] = $row;
  }

  foreach ($groups as $groups_row)
  {
     //поиск id_parent у id
     if ($groups_row['id'] == $id)
     {
       $id_parent_element_id = $groups_row['id_parent'];
     }
  }

 //добавление в массив элемента с аналогичным id_parent но меньшим id
 foreach ($groups as $groups_row)
 {                                                                                                                                                                       
     if ($groups_row['id_parent'] == $id_parent_element_id and $groups_row['id'] != $id and $groups_row['id'] < $id)
     {
       $groups_up[] = $groups_row;
     }
 }

 if(isset($groups_up))
 {
    return $groups_up;
 }
 return;
}

function get_down_sibling_older($id) 
{
  $db = new mysqli('localhost', 'root', '', 'z1');
  $result = mysqli_query($db, "SELECT * FROM `groups`");
  $db->close();
  
  $groups = array();
  $id_parent_element_id = '0';

  while ($row = mysqli_fetch_assoc($result)) 
  {
    $groups[] = $row;
  }

  foreach ($groups as $groups_row)
  {
     //поиск id_parent у id
     if ($groups_row['id'] == $id)
     {
       $id_parent_element_id = $groups_row['id_parent'];
     }
  }

 //добавление в массив элемента с аналогичным id_parent но больший id
 foreach ($groups as $groups_row)
 {                                                                                                                                                                       
     if ($groups_row['id_parent'] == $id_parent_element_id and $groups_row['id'] != $id and $groups_row['id'] > $id)
     {
       $groups_up[] = $groups_row;
     }
 }

 if(isset($groups_up))
 {
    return $groups_up;
 }
 return;
}

function get_up_sibling_parent($id) 
{
  $id = $_GET['id'];
  $db = new mysqli('localhost', 'root', '', 'z1');
  $result = mysqli_query($db, "SELECT * FROM `groups`");
  $db->close();
  
  $groups = array();
  $id_parent_element_id = '0';

  while ($row = mysqli_fetch_assoc($result)) 
  {
    $groups[] = $row;
  }

  foreach ($groups as $groups_row)
  {
     //поиск id_parent у id
     if ($groups_row['id'] == $id)
     {
       $id_parent_element_id = $groups_row['id_parent'];
     }
 }

 //добавление в массив элемента с аналогичным id_parent но меньшим id
 foreach ($groups as $groups_row)
 {                                                                                                                                                                       
     if ($groups_row['id_parent'] == $id_parent_element_id and $groups_row['id'] != $id and $groups_row['id'] < $id)
     {
       $groups_up[] = $groups_row;
     }
 }

 if(isset($groups_up))
 {
    return $groups_up;
 }
 return;
}

function get_down_sibling_parent($id) 
{
  $id = $_GET['id'];
  $db = new mysqli('localhost', 'root', '', 'z1');
  $result = mysqli_query($db, "SELECT * FROM `groups`");
  $db->close();
  
  $groups = array();
  $id_parent_element_id = '0';

  while ($row = mysqli_fetch_assoc($result)) 
  {
    $groups[] = $row;
  }

  foreach ($groups as $groups_row)
  {
     //поиск id_parent у id
     if ($groups_row['id'] == $id)
     {
       $id_parent_element_id = $groups_row['id_parent'];
     }
 }

 //добавление в массив элемента с аналогичным id_parent но больший id
 foreach ($groups as $groups_row)
 {                                                                                                                                                                       
     if ($groups_row['id_parent'] == $id_parent_element_id and $groups_row['id'] != $id and $groups_row['id'] > $id)
     {
       $groups_down[] = $groups_row;
     }
 }

 if(isset($groups_down))
 {
    return $groups_down;
 }
 return;
}

function get_parent_groups($id) 
{
  $id = $_GET['id'];
  $db = new mysqli('localhost', 'root', '', 'z1');
  $result = mysqli_query($db, "SELECT * FROM `groups`");
  $db->close();
  
  $groups = array();
  while ($row = mysqli_fetch_assoc($result)) 
  {
    $groups[] = $row;
  }

  $groups_down = array();

  foreach ($groups as $groups_row)
  {

      if ($groups_row['id'] == $id)
      {
        $groups_down[] = $groups_row;
      }
  }

  return $groups_down;
  
}



function get_child_groups($id) 
{
  $id = $_GET['id'];
  $db = new mysqli('localhost', 'root', '', 'z1');
  $result = mysqli_query($db, "SELECT * FROM `groups`");
  $db->close();
  
  $groups = array();
  while ($row = mysqli_fetch_assoc($result)) 
  {
    $groups[] = $row;
  }

  $groups_down = array();

  foreach ($groups as $groups_row)
  {

      if ($groups_row['id_parent'] == $id)
      {
        $groups_down[] = $groups_row;
      }
  }

  if(isset($groups_down))
  {
     return $groups_down;
  }
  return $groups_down;
  
}






function get_top_level_groups() {
  //Строка подлкючения к базе
  $db = new mysqli('localhost', 'root', '', 'z1');
  $result = mysqli_query($db, "SELECT id, name FROM `groups` WHERE id_parent=0");
  //Закрываем поключение к базе
  $db->close();
  $groups = array();
  while ($row = mysqli_fetch_assoc($result)) 
  {
    $groups[] = $row;
  }
  return $groups;
}









//id_group=$id
function get_count($id)
{
  $db = new mysqli('localhost', 'root', '', 'z1');

  $count=0;

  //Получяение всей(id, id_parent, name) таблицы группы
  $result = mysqli_query($db, "SELECT * FROM `groups`");
  $all_groups = array();
  //Разбиение массива на строки с элементами
  while ($all_groups_row = mysqli_fetch_assoc($result)) 
  {
    $all_groups[] = $all_groups_row;
  }    

  $count =  get_child_count($db, $id, $count, $all_groups);
  $db->close();

  return $count;
}

function get_child_count($db, $id, $count, $all_groups)
{
 
    $result = mysqli_query($db, "SELECT COUNT(*) as count  FROM `products` where id_group = $id");
    $row = $result->fetch_assoc();
    $count = $count + $row['count'];
    //echo $count . "! ";
    foreach ($all_groups as $all_groups_row)
    {
      if($all_groups_row['id_parent'] == $id)
      {
        $count = get_child_count($db, $all_groups_row['id'], $count, $all_groups);
      }
    }
    return $count;
}














function display_all_products()
{
  $products = get_products();
  echo "<table>";
  foreach ($products as &$row)
  {
    echo "<tr><td>" . $row['name'] . "</td></tr>";
  }
  echo "</table>";  

}

function get_products() {
  //Строка подлкючения к базе
  $db = new mysqli('localhost', 'root', '', 'z1');

  //Проверка на выбор ссылки
  if (isset($_GET['id'])) 
  {
    $id = $_GET['id'];

    //Получяение всей(id, id_group, name) таблицы продукты
    $result = mysqli_query($db, "SELECT * FROM `products`");
    $all_products = array();
    //Разбиение массива на строки с элементами
        while ($row = mysqli_fetch_assoc($result)) 
        {
          $all_products[] = $row;
        }

    //Получяение всей(id, id_parent, name) таблицы группы
    $result = mysqli_query($db, "SELECT * FROM `groups`");
    $all_groups = array();
    //Разбиение массива на строки с элементами
    while ($all_groups_row = mysqli_fetch_assoc($result)) 
    {
      $all_groups[] = $all_groups_row;
    }    
    
    //создание путсого массива и вхдение в функцию с рекурсией
    $products = array();
    $products = get_child_products($db, $id, $products, $all_products, $all_groups);
  }
  //Есил ссылка не выбрана
  else
  {
    //Отправка запроса
    $result = mysqli_query($db, "SELECT name FROM `products`");

    $products = array();
    //Разбиение массива на строки с элементами
    while ($row = mysqli_fetch_assoc($result)) 
    {
      $products[] = $row;
    }
  }

  //Закрываем поключение к базе
  $db->close();
  return $products;
}

function get_child_products($db, $id, $products, $all_products, $all_groups)
{
    //Поиск в группе по id = URL id
    $result = mysqli_query($db, "SELECT * FROM `groups` where id = $id");
    $groups = array();
    //Разбиение массива на строки с элементами
    while ($groups_row = mysqli_fetch_assoc($result)) 
    {
      $groups[] = $groups_row;
    }

    //Получение продуктов по id
    foreach ($groups as $groups_row)
    {
      foreach ($all_products as $all_products_row)
      {
        if ($all_products_row['id_group'] == $groups_row['id'])
        {
          $products[] = $all_products_row;
        }
      }
    }

    //начало проверки с возможным входом в рекурсию
    foreach ($groups as $groups_row)
    {
      foreach ($all_groups as $all_groups_row)
      {
        if($groups_row['id'] == $all_groups_row['id_parent'])
        {
          $products = get_child_products($db, $all_groups_row['id'], $products, $all_products, $all_groups);
        }
     }
    }
    return $products;
}
?>