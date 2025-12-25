<?php
session_start();

require 'config.php';
//input field validation
function validate($inputData)
{
    global $conn;
    $validateData = mysqli_real_escape_string($conn,$inputData);
    return trim($validateData);
}

//redirect from 1 page to another page with the message (status)
function redirect($url,$status)
{
    $_SESSION['status']=$status;
    header('Location:'.$url);
    exit(0);
}

// display the message after any process
function alertmessage()
{
    if(isset($_SESSION['status']))
    {
         $_SESSION['status'];
        echo'<div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h6>'.$_SESSION['status'].'</h6>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
        unset($_SESSION['status']);
    }
}

//insert record 
function insert($tableName, $data)
{
    global $conn;
    $table= validate($tableName);

    $columns = array_keys($data);
    $values= array_values($data);
    $finalColumn= implode(',',$columns);
    $finalValues= "'".implode("', '",$values)."'";

    $query="INSERT INTO $table ($finalColumn) VALUES($finalValues)";
    $result=mysqli_query($conn,$query);
    return $result;
}

//update record
function update($tableName,$id,$data)
{
    global $conn;
    $table=validate($tableName);
    $id=validate($id);
    $updateDataString="";

    foreach($data as $columns => $value)
    {
        $updateDataString.=$columns.'='."'$value',";
    }
    $finalUpdateData=substr(trim($updateDataString),0,-1);

    $query="UPDATE $table SET $finalUpdateData WHERE id='$id' ";
    $result= mysqli_query($conn, $query);
    return $result;
}

//select all
function getAll($tableName,$status = NULL)
{
    global $conn;
    $table=validate($tableName);
    $status=validate($status);

    if($status == 'status')
    {
        $query ="SELECT * FROM $table WHERE $status='0'";
    }
    else {
        $query= "SELECT * FROM $table";
    }
    return mysqli_query($conn,$query);
}

//get single row from table by id
function getById($tableName,$id)
{
    global $conn;
    $table=validate($tableName);
    $id=validate($id);

    $query="SELECT * FROM $table WHERE id='$id' LIMIT 1";
    $result=mysqli_query($conn, $query);

    if($result)
    {
        if(mysqli_num_rows($result)==1)
        {
            $row=mysqli_fetch_assoc($result);
            $response=[
                'status' => 200,
                'data' =>$row,
                'message' => 'Record Found'
            ];
            return $response;
        }
        else
        {
            $response=[
                'status' => 404,
                'message' => 'No data found'
            ];
            return $response;
        }
    }
    else
    {
        $response=[
            'status' => 500,
            'message' => 'Something  went wrong!'
        ];
        return $response;
    }

}

//delete data
function delete($tableName, $id)
{
    global $conn;
    $table=validate($tableName);
    $id=validate($id); 
    
    $query="DELETE FROM $table WHERE id='$id' LIMIT 1";
    $result=mysqli_query($conn,$query);
    return $result;
}
function checkParamId($type)
{
if(isset($_GET[$type]))
                            {
                                if($_GET[$type]!='')
                                {
                                    return $_GET[$type];
                                }
                                else {
                                    echo'<h5>No id found</h5>';
                                    return false;
                                }
                            }
                            else {
                                echo'<h5>No id given in params</h5>';
                                return false;
                            }
                        }
?>