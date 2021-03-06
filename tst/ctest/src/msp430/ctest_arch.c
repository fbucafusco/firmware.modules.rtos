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

/** \brief FreeOSEK Os Conformance Test
 **
 ** \file FreeOSEK/Os/tst/ctest/inc/posix/ctest_arch.h
 **/

/** \addtogroup FreeOSEK
 ** @{ */
/** \addtogroup FreeOSEK_Os
 ** @{ */
/** \addtogroup FreeOSEK_Os_CT Conformance Test
 ** @{ */

/*==================[inclusions]=============================================*/
#include "ciaaPlatforms.h"
#include "ctest_arch.h"
#if (CPU == msp430f5529)
#include "msp430.h"
#endif

/*==================[macros and definitions]=================================*/

/*==================[internal data declaration]==============================*/

/*==================[internal functions declaration]=========================*/

/*==================[internal data definition]===============================*/

/*==================[external data definition]===============================*/
#if (CPUTYPE == msp430f5x_6x)


/* Use P1.7 as interrupt for tests */
extern void EnableISR2_Arch(void)
{
	/* ACCESS TO THE PORT HW DIRECTLY, WITHOUT DRIVER*/
	P1IE 	|= 0x80;
}

/* Use P2.2 as interrupt for tests */
extern void EnableISR1_Arch(void)
{
	/* ACCESS TO THE PORT HW DIRECTLY, WITHOUT DRIVER*/
	P2IE 	|= 0x04;
}


/* Use P1.7 as interrupt for tests */
extern void TriggerISR2_Arch(void)
{
	/* ACCESS TO THE PORT HW DIRECTLY, WITHOUT DRIVER*/
	P1IFG |= 0x80;
}

/* Use P2.2 as interrupt for tests */
extern void TriggerISR1_Arch(void)
{
	/* ACCESS TO THE PORT HW DIRECTLY, WITHOUT DRIVER*/
	P2IFG |= 0x04;
}

#endif

/*==================[internal functions definition]==========================*/

/*==================[external functions definition]==========================*/

/** @} doxygen end group definition */
/** @} doxygen end group definition */
/** @} doxygen end group definition */
/*==================[end of file]============================================*/
