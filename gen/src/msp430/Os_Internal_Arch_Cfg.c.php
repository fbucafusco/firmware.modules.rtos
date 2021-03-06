/********************************************************
 * DO NOT CHANGE THIS FILE, IT IS GENERATED AUTOMATICALY*
 ********************************************************/

/* Copyright 2016, Franco Bucafusco
 * All rights reserved.
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

/** \brief FreeOSEK Os Generated Internal Achitecture Configuration Implementation File
 **
 ** \file msp430/Os_Internal_Arch_Cfg.c
 ** \arch msp430
 **/

/** \addtogroup FreeOSEK
 ** @{ */
/** \addtogroup FreeOSEK_Os
 ** @{ */
/** \addtogroup FreeOSEK_Os_Internal
 ** @{ */


/*==================[inclusions]=============================================*/
#include "Os_Internal.h"

#if(CPU == msp430f5x_6x)
/* THIS IS A DIRTY WORKAROUND :( ciaa/Firmware#309*/
#undef FALSE
#undef TRUE
#include "msp430.h"
#endif

/*==================[macros and definitions]=================================*/

/*==================[internal data declaration]==============================*/

/*==================[internal functions declaration]=========================*/


/*==================[internal data definition]===============================*/

/*==================[external data definition]===============================*/


/*==================[internal functions definition]==========================*/


interrupt_vec(UNMI_VECTOR) __attribute__((naked)) /*No Handler set for ISR UNMI_VECTOR (IRQ 20) */
void OSEK_ISR_UNMI_VECTOR(void)
{
   while (1)
   {
   }
}


interrupt_vec(SYSNMI_VECTOR) __attribute__((naked)) /*No Handler set for ISR SYSNMI_VECTOR (IRQ 21) */
void OSEK_ISR_SYSNMI_VECTOR(void)
{
   while (1)
   {
   }
}

/*==================[external functions definition]==========================*/
<?php
$this->loadHelper("modules/rtos/gen/ginc/Multicore.php");
$this->loadHelper("modules/rtos/gen/ginc/Platform.php");

/* get Interrupt list for platform  */
$intList = $this->helper->platform->getInterruptHandlerList();

$MAX_INT_COUNT = max(array_keys($intList))+1;
?>

/*** Non Used Interrupt handlers ***/

<?php
/* get ISRs defined by user application within the OIL file*/
$intnames = $this->helper->multicore->getLocalList("/OSEK", "ISR");
for($i=0; $i < $MAX_INT_COUNT; $i++)
{
   $src_found = 0;
   $intcat = 0 ;

   foreach($intnames as $int)
   {
      /*
      handlers that are present in the system (defined in the oil file)
      do not add extra code here.
      */
      $intcat = $this->config->getValue("/OSEK/" . $int,"CATEGORY");
      $source = $this->config->getValue("/OSEK/" . $int,"INTERRUPT");

      if($intList[$i] == $source)
      {
        if ($intcat == 2)
        {
            $src_found = 1;
        } elseif ($intcat == 1)
        {
            $src_found = 1;
        }
        else
        {
          $this->log->error("Interrupt $int type $inttype has an invalid category $intcat");
        }

        break; #the irq was found, break the inner loop
      }
   }

   if( $intList[$i]=="TIMER2_A1" || $intList[$i]=="TIMER2_A0" || $intList[$i]=="RESET" || $intList[$i]=="UNMI" || $intList[$i]=="SYSNMI" )
   {
      /*
      This part forces irq_handlers not to be defined here.
      TIMER2_XX:  Use for OSEK periodic interrupt
      RESET:      defined by gcc @ compile time.
      UNMI:       defined in this file
      SYSNMI:     defined in this file
      */
      $intcat = 0 ;
      $src_found = 1;
   }

   if($src_found == 0)
   {
      #for an undefined ISR witihn the OIL file, we defiene a DUMMY handler.
      print "interrupt_vec($intList[$i]_VECTOR)  __attribute__((naked))  \n";
      print "void OSEK_ISR_$intList[$i]_VECTOR(void) /*No Handler set for ISR $intList[$i]_VECTOR (IRQ $i) */ \n";
      print "{\n";
      print "   RETURN_FROM_NAKED_ISR();  /*return from ISR*/\n"; #this includes the RETI intruction. Is inserted here becase the naked attribute removes it when compile
      print "}\n\n";
   }
   else
   {
      if( $intcat == 1 )
      {
         #for an ISR type 1 witihn the OIL file, we defiene a ISR wrapper that calls the ISR defined by the user somewhere.
         print "interrupt_vec($intList[$i]_VECTOR) /*__attribute__((naked))*/\n";
         print "void OSEK_ISR_$intList[$i]_VECTOR_WRAPPER(void) /*Wrapper function for ISR $intList[$i]_VECTOR (IRQ $i). User should define ISR($intList[$i]_VECTOR) somewhere */ \n";
         print "{\n";
         print "   PreIsr1_Arch($i);\n";
         print "   OSEK_ISR_$intList[$i]_VECTOR();\n";
         print "   PostIsr1_Arch($i);\n";
         print "  /* RETURN_FROM_NAKED_ISR();*  /*return from ISR*/\n";  #this includes the RETI intruction. Is inserted here becase the naked attribute removes it when compile
         print "}\n\n";
      }
   }
}
?>

/** \brief Interrupt enabling and priority setting function */
void Enable_User_ISRs(void)
{
<?php
/* get ISRs defined by user application */
$intnames = $this->helper->multicore->getLocalList("/OSEK", "ISR");

foreach ($intnames as $int)
{
   $source  = $this->config->getValue("/OSEK/" . $int,"INTERRUPT");
   //$prio    = $config->getValue("/OSEK/" . $int,"PRIORITY");

   $key = array_search($source, $intList);
   if( $key !== false )
   {
      print "   /* Enabling IRQ $source */\n";
      print "   MSP430_EnableIRQ(" . $key. ");\n";
   }
   else
   {
      trigger_error("===== OIL ERROR: The IRQ name :$source is not valid for this processor =====\n", E_USER_ERROR);
   }
}
?>
}


/** \brief Enable user defined category 2 ISRs */
void Enable_ISR2_Arch(void)
{
<?php
/* get ISRs defined by user application */
$intnames = $this->helper->multicore->getLocalList("/OSEK", "ISR");
foreach ($intnames as $int)
{
   $source = $this->config->getValue("/OSEK/" . $int,"INTERRUPT");
   $cat = $this->config->getValue("/OSEK/" . $int,"CATEGORY");

   if($cat == 2)
   {
      $key = array_search($source, $intList);
      if( $key !== false )
      {
         print "   /* Enabling IRQ $source */\n";
         print "   MSP430_EnableIRQ(" . $key. ");\n";
      }
      else
      {
         trigger_error("===== OIL ERROR: The IRQ name :$source is not valid for this processor =====\n", E_USER_ERROR);
      }
   }
}
?>
}

/** \brief Disable user defined category 2 ISRs */
void Disable_ISR2_Arch(void)
{
<?php
/* get ISRs defined by user application */
$intnames = $this->helper->multicore->getLocalList("/OSEK", "ISR");
foreach ($intnames as $int)
{
   $source = $this->config->getValue("/OSEK/" . $int,"INTERRUPT");
   $cat = $this->config->getValue("/OSEK/" . $int,"CATEGORY");

   if($cat == 2)
   {
      $key = array_search($source, $intList);
      if( $key !== false )
      {
         print "   /* Disabling IRQ $source */\n";
         print "   MSP430_DisableIRQ(" .  $key . ");\n";
      }
      else
      {
         trigger_error("===== OIL ERROR: The IRQ name :$source is not valid for this processor =====\n", E_USER_ERROR);
      }
   }
}
?>
}

/** @} doxygen end group definition */
/** @} doxygen end group definition */
/** @} doxygen end group definition */
/*==================[end of file]============================================*/
