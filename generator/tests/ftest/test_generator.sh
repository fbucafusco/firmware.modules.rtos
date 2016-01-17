# Copyright 2015, 2016 Carlos Pantelides
# All rights reserved.
#
# This file is part of CIAA Firmware.
#
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions are met:
#
# 1. Redistributions of source code must retain the above copyright notice,
#    this list of conditions and the following disclaimer.
#
# 2. Redistributions in binary form must reproduce the above copyright notice,
#    this list of conditions and the following disclaimer in the documentation
#    and/or other materials provided with the distribution.
#
# 3. Neither the name of the copyright holder nor the names of its
#    contributors may be used to endorse or promote products derived from this
#    software without specific prior written permission.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
# AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
# IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
# ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
# LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
# CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
# SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
# INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
# CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
# ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
# POSSIBILITY OF SUCH DAMAGE.

# comment out the ">/dev/null" redirections and "rm ..." lines when fixing broken tests

tXXestBlinkingExampleFull() {
   rm -rf ${TMP}${DS}*

   php modules/rtos/generator/generator.php -v -DARCH=x86 -DCPUTYPE=ia32 -DCPU=none \
      -c ${FIXTURES}${DS}blinking.oil \
      -t ${FIXTURES}${DS}gen/inc/Os_Internal_Cfg.h.php \
         ${FIXTURES}${DS}gen/inc/Os_Cfg.h.php \
         ${FIXTURES}${DS}gen/src/Os_Cfg.c.php \
         ${FIXTURES}${DS}gen/src/Os_Internal_Cfg.c.php \
         ${FIXTURES}${DS}gen/src/x86/Os_Internal_Arch_Cfg.c.php \
         ${FIXTURES}${DS}gen/inc/x86/Os_Internal_Arch_Cfg.h.php \
      -b /gen/ \
      -o ${TMP} > /dev/null 2>&1

   GOT=${TMP}

   FILE=$(find "$GOT" -iname Os_Cfg.c)
   diff -w  ${EXPECTED}${DS}src/Os_Cfg.c                   "$FILE" > /dev/null
   assertTrue $? || echo  "FAIL: ${EXPECTED}${DS}src/Os_Cfg.c != $FILE"

   FILE=$(find "$GOT" -iname Os_Internal_Arch_Cfg.c)
   diff -w  ${EXPECTED}${DS}src/x86/Os_Internal_Arch_Cfg.c "$FILE" > /dev/null
   assertTrue $? || echo  "FAIL: ${EXPECTED}${DS}src/x86/Os_Internal_Arch_Cfg.c != $FILE"

   FILE=$(find "$GOT" -iname Os_Internal_Cfg.c)
   diff -w  ${EXPECTED}${DS}src/Os_Internal_Cfg.c          "$FILE" > /dev/null
   assertTrue $? || echo  "FAIL: ${EXPECTED}${DS}src/Os_Internal_Cfg.c != $FILE"

   FILE=$(find "$GOT" -iname Os_Internal_Arch_Cfg.h)
   diff -w  ${EXPECTED}${DS}inc/x86/Os_Internal_Arch_Cfg.h "$FILE" > /dev/null
   assertTrue $? || echo  "FAIL: ${EXPECTED}${DS}inc/x86/Os_Internal_Arch_Cfg.h != $FILE"

   FILE=$(find "$GOT" -iname Os_Cfg.h)
   diff -w  ${EXPECTED}${DS}inc/Os_Cfg.h                   "$FILE" > /dev/null
   assertTrue $? || echo  "FAIL: ${EXPECTED}${DS}inc/Os_Cfg.h != $FILE"

   FILE=$(find "$GOT" -iname Os_Internal_Cfg.h)
   diff -w  ${EXPECTED}${DS}inc/Os_Internal_Cfg.h          "$FILE" > /dev/null
   assertTrue $? || echo  "FAIL: ${EXPECTED}${DS}inc/Os_Internal_Cfg.h != $FILE"

   rm -rf ${TMP}${DS}*
}

tXXestOneTemplate() {
   rm -rf ${TMP}${DS}*

   php modules/rtos/generator/generator.php -v -DARCH=x86 -DCPUTYPE=ia32 -DCPU=none \
      -c ${FIXTURES}${DS}blinking.oil \
      -t ${FIXTURES}${DS}gen/inc/Os_Internal_Cfg.h.php \
      -b /gen/ \
      -o ${TMP} > /dev/null 2>&1

   GOT=${TMP}

   FILE=$(find "$GOT" -iname Os_Internal_Cfg.h)
   diff -w  ${EXPECTED}${DS}inc/Os_Internal_Cfg.h                   "$FILE" > /dev/null
   assertTrue $? || echo  "FAIL: ${EXPECTED}${DS}inc/Os_Internal_Cfg.h != $FILE"

   rm -rf ${TMP}${DS}*
}

testOutputPath() {
   php modules/rtos/generator/generator.php -v -DARCH=x86 -DCPUTYPE=ia32 -DCPU=none \
      -c ${FIXTURES}${DS}blinking.oil \
      -t ${FIXTURES}${DS}gen/inc/Os_Internal_Cfg.h.php \
      -b /gen/ \
      -o ${TMP} ; # > /dev/null 2>&1

   diff -w  ${EXPECTED}${DS}inc/Os_Internal_Cfg.h  ${TMP}${DS}inc/Os_Internal_Cfg.h > /dev/null
   assertTrue $? || echo  "FAIL: ${EXPECTED}${DS}inc/Os_Internal_Cfg.h  ${TMP}${DS}inc/Os_Internal_Cfg.h"

  # rm -rf ${TMP}${DS}*
}

testHelper() {
   php modules/rtos/generator/generator.php -v -DARCH=x85 -DCPUTYPE=ia32 -DCPU=none \
   -c ${FIXTURES}${DS}blinking.oil \
   -t ${FIXTURES}${DS}templates${DS}template_for_helper_testing.php \
   -b /templates/ \
   -H ${FIXTURES}${DS}gen${DS}DummyHelper.php \
   -o ${TMP} > /dev/null 2>&1

   diff -w  ${EXPECTED}${DS}template_for_helper_testing  ${TMP}${DS}template_for_helper_testing > /dev/null
   assertTrue $? || echo "ERROR: ${EXPECTED}${DS}template_for_helper_testing != ${TMP}${DS}template_for_helper_testing"

   rm -rf ${TMP}${DS}*

}

SHUNIT=$1
TESTS=$2
DS=$3
FIXTURES=$4
EXPECTED=$5
TMP=$6

shift $#

. ${SHUNIT}