<?php
 
/*
 
  Copyright (c) 2010, Andrew C Schools andy.mezey@gmail.com
 
  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:
 
  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.
 
  THE SOFTWARE IS PROVidED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE.
 
  @description: Find next cron run-time.
  @notes: Please report any bugs to andy.mezey@gmail.com
  @version: 1.01
 
*/
 
class CronParser
{
 
  public $cron_schedule;
  public $limit = 10000;
 
  function CronParser($cron_schedule)
  {
    $this->cron_schedule = $cron_schedule;
  }
 
  public function next_runtime($time=0)
  {
 
    $j=0; // used as an internal counter to prevent an endless loop
    $time = ( $time != 0 ? $time : time() ); // time right now
    // split our cron into variables
    list( $cron_minute, $cron_hour, $cron_day, $cron_month, $cron_day_of_week ) = split( " ", $this->cron_schedule );
 
    if( $cron_day_of_week == "0" ) $cron_day_of_week = "7"; // 7 and 0 both mean Sunday
 
    do
    {
 
      $j++; // internal counter
 
      // split our current time into variables (the $time variable will be updated after every iteration)
      list( $now_minute, $now_hour, $now_day, $now_month, $now_day_of_week ) = split( " ", date("i H d n N", $time ) );
 
      if( $cron_month != "*" )
      {
        if( (int)$cron_month != $now_month )
        {
          $now_month = (int)$now_month + 1; // increment the month by 1
          // set minute, hour and day to 0 so we start at the begining of the next month
          $time = mktime( 0, 0, 0, $now_month, 1, date("Y",$time) ); // $time + 1 month
          continue; // start again
        }
      }
 
      if( $cron_day != "*" )
      {
        if( (int)$cron_day != $now_day )
        {
          $now_day = (int)$now_day + 1; // increment the day by 1
          // set minute and hour to 0 so we start at the begining of the next day
          $time = mktime( 0, 0, 0, $now_month, $now_day, date("Y",$time) ); // $time + 1 day
          continue; // start again
        }
      }
 
      if( $cron_hour != "*" )
      {
        if( (int)$cron_hour != $now_hour )
        {
          $now_hour = (int)$now_hour + 1; // increment the hour by 1
          // set minute to 0 so we start at the begining of the next hour
          $time = mktime( $now_hour, 0, 0, $now_month, $now_day, date("Y",$time) ); // $time + 1 hour
          continue; // start again
        }
      }
 
      if( $cron_minute != "*" )
      {
        if( (int)$cron_minute != $now_minute )
        {
          $now_minute = (int)$now_minute + 1; // increment the minute by 1
          $time = mktime( $now_hour, $now_minute, 0, $now_month, $now_day, date("Y",$time) ); // $time + 1 minute
          continue; // start again
        }
      }
 
      // must be checked last
      if( $cron_day_of_week != "*" )
      {
        if( (int)$cron_day_of_week != $now_day_of_week )
        {
          $now_day = (int)$now_day + 1; // increment the day by 1
           // set minute and hour to 0 so we start at the begining of the next day
          $time = mktime( 0, 0, 0, $now_month, $now_day, date("Y",$time) ); // $time + 1 day
          continue; // start again
        }
      }
 
      /* If we reach this point, all the conditions
         above are true and we have our next cron time!
      */
      return $time;
 
    } while( $j < $this->limit );
 
    return false;
 
  }
 
  public function last_runtime($time=0)
  {
 
    $j=0; // used as an internal counter to prevent an endless loop
    $time = ( $time != 0 ? $time : time() ); // time right now
    // split our cron into variables
    list( $cron_minute, $cron_hour, $cron_day, $cron_month, $cron_day_of_week ) = split( " ", $this->cron_schedule );
 
    if( $cron_day_of_week == "0" ) $cron_day_of_week = "7"; // 7 and 0 both mean Sunday
 
    do
    {
 
      $j++; // internal counter
 
      // split our current time into variables (the $time variable will be updated after every iteration)
      list( $now_minute, $now_hour, $now_day, $now_month, $now_day_of_week ) = split( " ", date("i H d n N", $time ) );
 
      if( $cron_month != "*" )
      {
        if( (int)$cron_month != $now_month )
        {
          $now_month = (int)$now_month - 1; // increment the month by 1
          $num_days_in_month = (int)date("t",mktime( 0, 0, 0, $now_month, 1, date("Y",$time) ) ); /// number of days in month
          // set minute, hour and day to their highest value so we start at the end of the next month
          $time = mktime( 23, 59, 59, $now_month, $num_days_in_month, date("Y",$time) ); // $time + 1 month
          continue; // start again
        }
      }
 
      if( $cron_day != "*" )
      {
        if( (int)$cron_day != $now_day )
        {
          $now_day = (int)$now_day - 1; // increment the day by 1
          // set minute and hour to their highest value so we start at the begining of the next day
          $time = mktime( 23, 59, 59, $now_month, $now_day, date("Y",$time) ); // $time + 1 day
          continue; // start again
        }
      }
 
      if( $cron_hour != "*" )
      {
        if( (int)$cron_hour != $now_hour )
        {
          $now_hour = (int)$now_hour - 1; // increment the hour by 1
          // set minute and second to their highest value so we start the next hour
          $time = mktime( $now_hour, 59, 59, $now_month, $now_day, date("Y",$time) ); // $time + 1 hour
          continue; // start again
        }
      }
 
      if( $cron_minute != "*" )
      {
        if( (int)$cron_minute != $now_minute )
        {
          $now_minute = (int)$now_minute - 1; // increment the minute by 1
          $time = mktime( $now_hour, $now_minute, 59, $now_month, $now_day, date("Y",$time) ); // $time + 1 minute
          continue; // start again
        }
      }
 
      // must be checked last
      if( $cron_day_of_week != "*" )
      {
        if( (int)$cron_day_of_week != $now_day_of_week )
        {
          $now_day = (int)$now_day - 1; // increment the day by 1
           // set minute and hour to 0 so we start at the begining of the next day
          $time = mktime( 0, 0, 0, $now_month, $now_day, date("Y",$time) ); // $time + 1 day
          continue; // start again
        }
      }
 
     /* If we reach this point, all the conditions
         above are true and we have our next cron time!
      */
 
      return $time;
 
    } while( $j < $this->limit );
 
    return false;
 
  }
 
}
 
/*
$CronParser = new CronParser("0 8 * * *");
$next_time = $CronParser->next_runtime();
$last_time = $CronParser->last_runtime();
var_dump( date( "m/d/Y H:i T", $next_time ) );
var_dump( date( "m/d/Y H:i T", $last_time ) );
*/
 
?>