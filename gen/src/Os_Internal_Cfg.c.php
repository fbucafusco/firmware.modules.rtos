/********************************************************
 * DO NOT CHANGE THIS FILE, IT IS GENERATED AUTOMATICALY*
 ********************************************************/

/* Copyright 2008, 2009, 2014, 2015 Mariano Cerdeiro
 * Copyright 2014, ACSE & CADIEEL
 *      ACSE: http://www.sase.com.ar/asociacion-civil-sistemas-embebidos/ciaa/
 *      CADIEEL: http://www.cadieel.org.ar
 * Copyright 2016 Franco Bucafusco
 *
 * All Rights Reserved
 *
 * This file is part of CIAA Firmware.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its
 *    contributors may be used to endorse or promote products derived from this
 *    software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 */

/** \brief FreeOSEK Os Generated Internal Configuration Implementation File
 **
 ** \file Os_Internal_Cfg.c
 **
 **/

/** \addtogroup FreeOSEK
 ** @{ */
/** \addtogroup FreeOSEK_Os
 ** @{ */
/** \addtogroup FreeOSEK_Os_Internal
 ** @{ */

/*==================[inclusions]=============================================*/
#include "Os_Internal.h"

/*==================[macros and definitions]=================================*/

/*==================[internal data declaration]==============================*/

/*==================[internal functions declaration]=========================*/

/*==================[internal data definition]===============================*/
<?php

$this->loadHelper("modules/rtos/gen/ginc/Multicore.php");
$this->loadHelper("modules/rtos/gen/ginc/Platform.php");

/* get Interrupt list for platform  */
$intList = $this->helper->platform->getInterruptHandlerList();

/* get tasks */
$tasks = $this->helper->multicore->getLocalList("/OSEK", "TASK");

foreach ($tasks as $task)
{
   print "\n/** \brief $task stack */\n";
   print "#if ( x86 == ARCH )\n";
   print "uint8 StackTask" . $task . "[" . $this->config->getValue("/OSEK/" . $task, "STACK") ." + TASK_STACK_ADDITIONAL_SIZE];\n";
   print "#else\n";
   if( $this->definitions["ARCH"]== "msp430")
   {
      #msp430 requieres bus alligmented memory access. So, if the compiler allocates this array as uint8 it could start at an even address.
      #we force the compiler to locate this array alligned declaring it as an uint16 (with the size divided by two)
      print "uint16 StackTask" . $task . "[" . $this->config->getValue("/OSEK/" . $task, "STACK") ."/2];\n";
   }
   else
   {
      print "uint8 StackTask" . $task . "[" . $this->config->getValue("/OSEK/" . $task, "STACK") ."];\n";
   }

   print "#endif\n";
}

foreach ($tasks as $task)
{
   print "\n/** \brief $task context */\n";
   print "TaskContextType ContextTask" . $task . ";\n";
}

$priority = $this->config->priority2osekPriority($tasks);

/* Ready List */
foreach ($priority as $prio)
{
   print "\n/** \brief Ready List for Priority $prio */\n";
   $count = 0;
   foreach ($tasks as $task)
   {
      if ($priority[$this->config->getValue("/OSEK/" . $task, "PRIORITY")] == $prio)
      {
         $count += $this->config->getValue("/OSEK/" . $task, "ACTIVATION");
      }
   }
   print "TaskType ReadyList" . $prio . "[" . $count . "];\n";
}

$counters = $this->helper->multicore->getLocalList("/OSEK", "COUNTER");
$alarms = $this->helper->multicore->getLocalList("/OSEK", "ALARM");

foreach ($counters as $counter)
{
   $countalarms = 0;

   foreach ($alarms as $alarm)
   {
      if ($counter == $this->config->getValue("/OSEK/" . $alarm,"COUNTER"))
      {
         $countalarms++;
      }
   }

   print "\nconst AlarmType OSEK_ALARMLIST_" . $counter . "[" . $countalarms . "] = \n";
   print "{\n";

   foreach ($alarms as $alarm)
   {
      if ($counter == $this->config->getValue("/OSEK/" . $alarm,"COUNTER"))
      {
         print "   $alarm, /* this alarm has to be incremented with this counter */\n";
      }
   }

   print "};\n\n";
}
?>

/*==================[external data definition]===============================*/
/* FreeOSEK to configured priority table
 *
 * This table show the relationship between the user selected
 * priorities and the OpenOSE priorities:
 *
 * User P.         Osek P.
<?php
$pkey = array_keys($priority);
for($i = 0; $i < count($priority); $i++)
{
   print " * " . $pkey[$i] . "               " . $priority[$pkey[$i]] . "\n";
}
print " */\n";

?>

const TaskConstType TasksConst[TASKS_COUNT] = {
<?php

/* create task const structure */
foreach ($tasks as $count=>$task)
{
   if ( $count != 0 ) print ",\n";
   print "   /* Task $task */\n";
   print "   {\n";
   print "       OSEK_TASK_$task,   /* task entry point */\n";
   print "       &ContextTask" . $task . ", /* pointer to task context */\n";
   print "       (StackPtrType) StackTask" . $task . ", /* pointer stack memory */\n";
   print "       sizeof(StackTask" . $task . "), /* stack size */\n";
   print "       " . $priority[$this->config->getValue("/OSEK/" . $task, "PRIORITY")] . ", /* task priority */\n";
   print "       " . $this->config->getValue("/OSEK/" . $task, "ACTIVATION"). ", /* task max activations */\n";
   print "       {\n";
   $extended = $this->config->getValue("/OSEK/" . $task, "TYPE");
   if($extended == "BASIC")
   {
      print "         0, /* basic task */\n";
   }
   elseif ($extended == "EXTENDED")
   {
      print "         1, /* extended task */\n";
   }
   else
   {
     $this->log->error("Wrong definition of task type \"" . $extended . "\" for task \"" . $task . "\".");
   }
   $schedule = $this->config->getValue("/OSEK/" .$task, "SCHEDULE");
   if ($schedule == "FULL")
   {
      print "         1, /* preemtive task */\n";
   }
   elseif($schedule == "NON")
   {
      print "         0, /* non preemtive task */\n";
   }
   else
   {
     $this->log->error("Wrong definition of task schedule \"" . $schedule . "\" for task \"" . $task . "\".");
   }
   print "         0\n";
   print "      }, /* task const flags */\n";
   $events = $this->config->getList("/OSEK/" . $task, "EVENT");
   $elist = "0 ";
   foreach ($events as $event)
   {
      $elist .= "| $event ";
   }
   print "      $elist, /* events mask */\n";
   $rlist = "0 ";
   $resources = $this->config->getList("/OSEK/" . $task, "RESOURCE");
   foreach($resources as $resource)
   {
      $rlist .= "| ( 1 << $resource ) ";
   }
   print "      $rlist,/* resources mask */\n";
   if (isset($this->definitions["MCORE"]))
   {
      print "      " . $this->config->getValue("/OSEK/" . $task, "CORE") . " /* core */\n";
   }
   else
   {
      print "      0 /* core */\n";
   }
   print "   }";
   print "\n";
}
print "\n";
?>
};

<?php
$rtasks = $this->helper->multicore->getRemoteList("/OSEK", "TASK");
$rtasks_count = count($rtasks);

if( $rtasks_count>0 )
{
   print("\n/** \brief RemoteTaskCore Array */\n");
   print("const TaskCoreType RemoteTasksCore[REMOTE_TASKS_COUNT] = {\n");

   for($i=0; $i<$rtasks_count; $i++)
   {
      print $this->config->getValue("/OSEK/$rtasks[$i]", "CORE");
      if ($i < (count($rtasks)-1))
      {
         print ", ";
      }
   }

   print("};\n");
}
else
{
   print "\n/* OIL FILE: THERE ARE NO REMOTE TASKS DEFINED IN THE SYSTEM */\n";
   //trigger_error("===== OIL INFO: There are no REMOTE TASKS define in the OIL file =====\n", E_USER_NOTICE);
}
?>


/** \brief TaskVar Array */
TaskVariableType TasksVar[TASKS_COUNT];

<?php
$appmodes = $this->config->getList("/OSEK", "APPMODE");

foreach ($appmodes as $appmode)
{
   $tasksinmode = array();
   foreach($tasks as $task)
   {
      $taskappmodes = $this->config->getList("/OSEK/" . $task, "APPMODE");
      foreach ($taskappmodes as $taskappmode)
      {
         if ($taskappmode == $appmode)
         {
            $tasksinmode[] = $task;
         }
      }
   }
   if (count($tasksinmode) > 0)
   {
      print "/** \brief List of Auto Start Tasks in Application Mode $appmode */\n";
      print "const TaskType TasksAppMode" . $appmode . "[" . count($tasksinmode). "]  = {\n";
      foreach($tasksinmode as $count=>$task)
      {
         if ($count != 0) print ",\n";
         print "   $task";
      }
      print "\n};\n";
   }
}

print "/** \brief AutoStart Array */\n";
print "const AutoStartType AutoStart[" . count($appmodes) . "]  = {\n";

foreach ($appmodes as $count=>$appmode)
{
   if ( $count != 0 ) print ",\n";
   print "   /* Application Mode $appmode */\n";
   print "   {\n";
   $tasksinmode = array();
   foreach($tasks as $task)
   {
      $taskappmodes = $this->config->getList("/OSEK/" . $task, "APPMODE");
      foreach ($taskappmodes as $taskappmode)
      {
         if ($taskappmode == $appmode)
         {
            $tasksinmode[] = $task;
         }
      }
   }
   print "      " . count($tasksinmode) .", /* Total Auto Start Tasks in this Application Mode */\n";
   if( count($tasksinmode)>0 )
   {
      print "      (TaskRefType)TasksAppMode" . $appmode . " /* Pointer to the list of Auto Start Stacks on this Application Mode */\n";
   }
   else
   {
      print "      NULL /* no tasks on this mode */\n";
   }
   print "   }";
}
print "\n};\n";
?>

<?php
print "const ReadyConstType ReadyConst[" . count($priority) .  "] = { \n";
$c = 0;
foreach ($priority as $prio)
{
   if ($c++ != 0) print ",\n";
   print "   {\n";
   $count = 0;
   foreach ($tasks as $task)
   {
      if ($priority[$this->config->getValue("/OSEK/" . $task, "PRIORITY")] == $prio)
      {
         $count += $this->config->getValue("/OSEK/" . $task, "ACTIVATION");
      }
   }
   print "      $count, /* Length of this ready list */\n";
   print "      ReadyList" . $prio . " /* Pointer to the Ready List */\n";
   print "   }";
}
print "\n};\n\n";

print "/** TODO replace next line with: \n";
print " ** ReadyVarType ReadyVar[" . count($priority) . "] ; */\n";
print "ReadyVarType ReadyVar[" . count($priority) . "];\n";
?>

<?php
/* Resources Priorities */
$resources = $this->config->getList("/OSEK","RESOURCE");
$resources_count = count($resources);

if( $resources_count>0 )
{
   print "/** \brief Resources Priorities */\n";
   print "const TaskPriorityType ResourcesPriority[" . count($resources) . "]  = {\n";
   $c = 0;
   foreach ($resources as $resource)
   {
      $count = 0;
      foreach ($tasks as $task)
      {
      $resorucestask = $this->config->getList("/OSEK/" . $task, "RESOURCE");
         foreach($resorucestask as $rt)
         {
            if ($rt == $resource)
            {
            if ($priority[$this->config->getValue("/OSEK/" . $task, "PRIORITY")] > $count)
               {
               $count = $priority[$this->config->getValue("/OSEK/" . $task, "PRIORITY")];
               }
            }
         }
      }
      if ($c++ != 0) print ",\n";
      print "   $count";

   }
   print "\n};\n";
}
else
{
   print "\n/* OIL FILE: THERE ARE NO RESOURCES DEFINED IN THE SYSTEM */\n";
   trigger_error("===== OIL INFO: There are no RESOURCES define in the OIL file =====\n", E_USER_NOTICE);
}

$alarms = $this->helper->multicore->getLocalList("/OSEK", "ALARM");
$alarms_count = count($alarms);
$alarms_autostart = 0;

if( $alarms_count> 0 )
{
   print "/** TODO replace next line with: \n";
   print " ** AlarmVarType AlarmsVar[" . count($alarms) . "]; */\n";
   print "AlarmVarType AlarmsVar[" . count($alarms) . "];\n\n";

   print "const AlarmConstType AlarmsConst[" . count($alarms) . "]  = {\n";

   foreach ($alarms as $count=>$alarm)
   {
      if ($count != 0)
      {
         print ",\n";
      }
      if ($this->config->getValue("/OSEK/" . $alarm, "AUTOSTART") == "TRUE")
      {
         $alarms_autostart++;
      }
      print "   {\n";
      print "      OSEK_COUNTER_" . $this->config->getValue("/OSEK/" . $alarm, "COUNTER") . ", /* Counter */\n";
      $action = $this->config->getValue("/OSEK/" . $alarm, "ACTION");
      print "      " . $action . ", /* Alarm action */\n";
      print "      {\n";
      switch ($action)
      {
      case "INCREMENT":
         print "         NULL, /* no callback */\n";
         print "         0, /* no task id */\n";
         print "         0, /* no event */\n";
         print "         OSEK_COUNTER_" . $this->config->getValue("/OSEK/" . $alarm . "/INCREMENT","COUNTER") . " /* counter */\n";
         break;
      case "ACTIVATETASK":
         print "         NULL, /* no callback */\n";
         print "         " . $this->config->getValue("/OSEK/" . $alarm . "/ACTIVATETASK","TASK") . ", /* TaskID */\n";
         print "         0, /* no event */\n";
         print "         0 /* no counter */\n";
         break;
      case "SETEVENT":
         print "         NULL, /* no callback */\n";
         print "         " . $this->config->getValue("/OSEK/" . $alarm . "/SETEVENT","TASK") . ", /* TaskID */\n";
         print "         " . $this->config->getValue("/OSEK/" . $alarm . "/SETEVENT","EVENT") . ", /* no event */\n";
         print "         0 /* no counter */\n";
         break;
      case "ALARMCALLBACK":
         print "         OSEK_CALLBACK_" . $this->config->getValue("/OSEK/" . $alarm . "/ALARMCALLBACK", "ALARMCALLBACKNAME") . ", /* callback */\n";
         print "         0, /* no taskid */\n";
         print "         0, /* no event */\n";
         print "         0 /* no counter */\n";
         break;
      default:
        $this->log->error("Alarm $alarm has an invalid action: $action");
         break;
      }
      print "      },\n";
      print "   }";

   }
   print "\n};\n\n";

   #print("#if( ALARM_AUTOSTART_COUNT>0 )\n");
   if($alarms_autostart>0)
   {
      print "const AutoStartAlarmType AutoStartAlarm[ALARM_AUTOSTART_COUNT] = {\n";
      $first = true;
      foreach ($alarms as $count=>$alarm)
      {
         if ($this->config->getValue("/OSEK/" . $alarm, "AUTOSTART") == "TRUE")
         {
            if ($first == false)
            {
               print ",\n";
            }
            else
            {
               $first = false;
            }
            print "  {\n";

            print "      " . $this->config->getValue("/OSEK/" . $alarm, "APPMODE") . ", /* Application Mode */\n";
            // print "      OSEK_COUNTER_" . $this->config->getValue("/OSEK/" . $alarm, "COUNTER") . ", /* Counter */\n";
            print "      $alarm, /* Alarms */\n";
            print "      " . $this->config->getValue("/OSEK/" . $alarm, "ALARMTIME") . ", /* Alarm Time */\n";
            print "      " . $this->config->getValue("/OSEK/" . $alarm, "CYCLETIME") . " /* Alarm Time */\n";
            print "   }";
         }
      }
      print "\n};\n\n";
   }
   else
   {
      print "\n/* OIL FILE: THERE ARE NO AUTOSTART ALARMS DEFINED IN THE SYSTEM */\n\n";
   }

$counters = $this->helper->multicore->getLocalList("/OSEK", "COUNTER");

   print "CounterVarType CountersVar[" . count($counters) . "];\n\n";

$alarms = $this->config->getList("/OSEK","ALARM");

   print "const CounterConstType CountersConst[" . count($counters) . "] = {\n";
   foreach ($counters as $count=>$counter)
   {
      if ($count!=0)
      {
         print ",\n";
      }
      print "   {\n";
      $countalarms = 0;
      foreach ($alarms as $alarm)
      {
      if ($counter == $this->config->getValue("/OSEK/" . $alarm,"COUNTER"))
         {
            $countalarms++;
         }
      }
      print "      $countalarms, /* quantity of alarms for this counter */\n";
      print "      (AlarmType*)OSEK_ALARMLIST_" . $counter . ", /* alarms list */\n";
      print "      " . $this->config->getValue("/OSEK/" . $counter,"MAXALLOWEDVALUE") . ", /* max allowed value */\n";
      print "      " . $this->config->getValue("/OSEK/" . $counter,"MINCYCLE") . ", /* min cycle */\n";
      print "      " . $this->config->getValue("/OSEK/" . $counter,"TICKSPERBASE") . " /* ticks per base */\n";
      print "   }";
   }
   print "\n};\n\n";
}
else
{
   print "\n/* OIL FILE: THERE ARE NO ALARMS DEFINED IN THE SYSTEM */\n";
   trigger_error("===== OIL INFO: There are no ALARMS define in the OIL file =====\n", E_USER_NOTICE);
}
?>

/** TODO replace the next line with
 ** uint8 ApplicationMode; */
uint8 ApplicationMode;

/** TODO replace the next line with
 ** uint8 ErrorHookRunning; */
/* This variable needs to be initialized to 0. This is the normal
 * behaviour in C, but in embedded C sometimes to save startup time
 * variables which do not have initialization are not initialized.
 */
uint8 ErrorHookRunning = 0;

/*==================[external functions definition]==========================*/

/*==================[internal functions definition]==========================*/
<?php

#we get an array of ISRs defined in the OIL file.
$intnames = $this->helper->multicore->getLocalList("/OSEK", "ISR");

if( count($intnames)>0 ) /*it only process averything if there is any ISR define within the OIL */
{
#for each ISR define in the OIL, we define the IRQ handler.
foreach ($intnames as $int)
{
   $inttype = $this->config->getValue("/OSEK/" . $int,"INTERRUPT");
   $intcat = $this->config->getValue("/OSEK/" . $int,"CATEGORY");

 //print $inttype ." ".$intcat ." ". $intList[$i] ." ".$int  ."\n";

   print("/* Wrapper ISR handler for $int */\n");

   if ($intcat == 2)
   {
      if($this->definitions["ARCH"] == "msp430")
      {
         print "interrupt_vec(".$inttype."_VECTOR)  \n";
         //print "interrupt_vec($int) \n";
      }
?>
void OSEK_ISR2_<?php print $int?>(void)
{
<?php
      $key = array_search( $inttype , $intList );

      if( $key !== false )
      {
         print("   PreIsr2_Arch( $key );\n");
      }
      else
      {
         trigger_error("===== OIL ERROR: The IRQ name :$inttype is not valid for this processor =====\n", E_USER_ERROR);
      }
?>

   /* store the calling context in a variable */
   ContextType actualContext = GetCallingContext();

   /* set isr 2 context */
   SetActualContext(CONTEXT_ISR2);

   /* trigger isr 2 */
   OSEK_ISR_<?php print $int;?>();

   /* reset context */
   SetActualContext(actualContext);

   <?php
      print("PostIsr2_Arch( $key );\n");
   ?>

   AfterIsr2_Schedule() ;
}

<?php
      } //if ($intcat == 2)
   } //loop
} //if count
?>

/** @} doxygen end group definition */
/** @} doxygen end group definition */
/** @} doxygen end group definition */
/*==================[end of file]============================================*/
