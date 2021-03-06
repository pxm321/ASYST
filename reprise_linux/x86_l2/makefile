#
#	RLM Binary Kit makefile - Unix systems.
#
#	You should only have to edit the first definition (ISV) to
#	correspond to your ISV name.  If you have a demo version of
#	RLM, do not change the ISV line below.
#
#	If you have defined an ISV-defined license transfer module,
#	you need to define the object file name here as well.
#

#
#	Beginning of customer-edited definitions
#

ISV = issrs

#
#	If you are not using ISV-defined license transfer, the above
#	definitions are the only ones you need to edit.  If you are
#	using ISV-defined transfer, there are more definitions below
#
#
#	Do NOT edit the next 10 lines
#
EXECS = rlmgenkeys rlc rlmgen rlmclient rlmsign roam_example $(ISV) $(ISV).set \
	rlmstat rlmverify actpro_demo

#
#	Targets for building in a cross-dev/target environment
#
ISV_NOSTRIP = $(ISV).nostrip
ISV_STRIP = $(ISV).strip

STEP1EXECS = rlmgenkeys

STEP3EXECS = rlc rlmgen rlmclient rlmsign roam_example $(ISV_NOSTRIP) rlmstat rlmverify $(ISV_STRIP)

TARGETEXECS = $(ISV).set

SHAREDEXT = so

VER = 1233

SO = librlm$(VER).$(SHAREDEXT)

all: $(EXECS) 

#
#	If you are doing cross-development, then:
#
#	1. "make step1" on the cross-dev system
#	2. copy rlmgenkeys to the target; run rlmgenkeys; copy rlm_privkey.c and
#			rlm_pubkey.c back to the cross-dev system into the
#			src directory.
#	3. "make step3" on the cross-dev system
#	4. copy the kit to the target
#	5. "make step5" on the target
#
#	Your kit is now built on the target and ready to use.
#
#	Note: skip steps 1 and 2 if you have a key pair from another
#		rlm platform, and put the keys into the src directory on the
#		cross-dev system; start	from step 3 above.
#
step1:	$(STEP1EXECS)
step3:	$(STEP3EXECS)
step5:	$(TARGETEXECS)

#
#	If you want to support license transfer from another license
#	manager, put the definitions of the interface modules here.
#
#	Put your ISV-defined transfer module object/source/library file names
#	here, plus add any rules to compile/link them.
#	Then, uncomment the $(ISV_XFER_OBJ) rule.
#
#	ISV_XFER_OBJ: the object for your isv transfer definitions.
#	ISV_XFER_SRC: source to the isv transfer defintions
#	ISV_XFER_LIBS: any libraries required for your ISV-transfer LM
#
ISV_XFER_SRC =
ISV_XFER_OBJ =
ISV_XFER_LIBS =
#
#	Uncomment the next line if you are using ISV-defined transfer.
#
#$(ISV_XFER_OBJ):	$(ISV_XFER_SRC)	
#	any special compile rules go here for your ISV-transfer module.

#
#	For example, to build the rlm-rlm example ISV transfer module,
#	set the above definitions as follows:
#
#	ISV_XFER_SRC = ../examples/rlm_transfer.c
#	ISV_XFER_OBJ = rlm_transfer.o
#	ISV_XFER_LIBS =			# No libs needed, since it's all in
#					# the standard rlm libraries.  You
#					# will need the libraries from the
#					# other license manager here.
#	$(ISV_XFER_OBJ):	$(ISV_XFER_SRC)
#		$(CC) -c $(ISV_XFER_SRC) $(CFLAGS)
#

#
#	End of customer-edited definitions
#

CFLAGS = -g -I../src -pthread -Wall -m32 -Dx86_l2 -fPIC

ARFLAGS = cr

ARXFLAGS = x

STRIPFLAGS = 

CC = cc

LD = $(CC)

LDFLAGS = -m32

LIBRARY = rlm.a

NOSSL = rlm_nossl.a

MAINSLIBRARY = rlmmains.a

ALTLIBRARY = librlm.a

XTRALIB = -lpthread -ldl -lrt rlmid1.a

OBJS = rlmclient.o rlm_isv_config.o roam_example.o rehost_example.o

KEYOBJS = rlm_privkey.o rlm_pubkey.o 

WEBOBJS = asp.o balloc.o base64.o cgi.o default.o ejlex.o ejparse.o emfdb.o \
          form.o h.o handler.o main.o mime.o misc.o page.o ringq.o rom.o \
          security.o sock.o sockGen.o sym.o uemf.o um.o umui.o url.o value.o \
          webrom.o webs.o webserver.o websuemf.o

RLMWEBOBJS = rlm_web_form.o rlm_web_options.o rlm_web_platforms.o \
             rlm_web_prompt.o rlm_web_rlminfo.o rlm_web_table.o rlm_web_utils.o

UTILEXECS = rlmdebug rlmdown rlmhostid rlmnewlog rlmremove rlmreread rlmstat rlmswitch \
	    rlmswitchr 

SHAREDFLAG = -shared

SHAREDXTRALIB = 

JAVALD = cc

$(LIBRARY):	$(KEYOBJS) rlm_isv_config.o
	ar $(ARFLAGS) $(LIBRARY) $(KEYOBJS) rlm_isv_config.o
	@rm -f $(ALTLIBRARY)
	@ln -s $(LIBRARY) $(ALTLIBRARY)

$(NOSSL):	$(LIBRARY)
	mkdir _t
	cd _t; ar $(ARXFLAGS) ../$(LIBRARY); mkdir _t2; cp rlm*.o _t2; cd _t2; ar $(ARFLAGS) $(NOSSL) *.o; mv $(NOSSL) ../..
	ranlib $(NOSSL)
	rm -rf _t

../src/rlm_privkey.c:
	@echo ""
	@echo "******************************************************"
	@echo "Your public/private key pair does not exist in ../src"
	@echo "We will generate them for you now..."
	@echo ""
	@echo "If you have previously generated these key files,"
	@echo "place them in ../src now and re-run this make"
	@echo "******************************************************"
	@echo ""
	cd ../src; ../x86_l2/rlmgenkeys
	chmod -w ../src/rlm_pubkey.c ../src/rlm_privkey.c

hosted_activation_demo_keys:
	rm -f ../src/rlm_pubkey.c ../src/rlm_privkey.c
	cp ../examples/hosted_demo_pubkey.c ../src/rlm_pubkey.c
	cp ../examples/hosted_demo_privkey.c ../src/rlm_privkey.c
	chmod -w ../src/rlm_pubkey.c ../src/rlm_privkey.c

rlc:	$(LIBRARY)
	ar $(ARXFLAGS) $(MAINSLIBRARY) rlc.o
	ar $(ARXFLAGS) $(LIBRARY) rlm_genlic.o
	ar $(ARXFLAGS) $(LIBRARY) rlmact.o
	$(LD) $(LDFLAGS) -o rlc rlc.o rlm_genlic.o rlmact.o \
						$(LIBRARY) $(XTRALIB)
	rm -f rlc.o rlm_genlic.o rlmact.o
	
rlmgen:	$(LIBRARY)
	ar $(ARXFLAGS) $(MAINSLIBRARY) rlc.o
	ar $(ARXFLAGS) $(LIBRARY) rlm_genlic.o
	ar $(ARXFLAGS) $(LIBRARY) rlmgen.o
	$(LD) $(LDFLAGS) -o rlmgen rlc.o rlm_genlic.o rlmgen.o \
						$(LIBRARY) $(XTRALIB)
	rm -f rlc.o rlm_genlic.o rlmgen.o
	
actpro_demo: actpro_demo.o $(LIBRARY)
	$(LD) $(LDFLAGS) -o actpro_demo actpro_demo.o $(LIBRARY) $(XTRALIB)

actpro_demo.o:  ../examples/actpro_demo.c
	$(CC) $(CFLAGS) -c ../examples/actpro_demo.c

rehost_example.o:	../examples/rehost_example.c
	$(CC) $(CFLAGS) -c ../examples/rehost_example.c

rlm_pubkey.o:	../src/rlm_pubkey.c
	$(CC) $(CFLAGS) -c ../src/rlm_pubkey.c

rlm_privkey.o:	../src/rlm_privkey.c
	$(CC) $(CFLAGS) -c ../src/rlm_privkey.c

rlm_isv_config.o:	../src/rlm_isv_config.c ../src/license_to_run.h
	$(CC) $(CFLAGS) -c ../src/rlm_isv_config.c

rlmclient.o:	../examples/rlmclient.c
	$(CC) $(CFLAGS) -c ../examples/rlmclient.c

roam_example.o:	../examples/roam_example.c
	$(CC) $(CFLAGS) -c ../examples/roam_example.c

rehost_example:	rehost_example.o $(LIBRARY)
	$(LD) $(LDFLAGS) -o rehost_example rehost_example.o $(LIBRARY) $(XTRALIB)
	
#
# Note: No dependency on $(LIBRARY) for rlmgenkeys so that we
#	can get the key pair source files created first.
#
rlmgenkeys:
	ar $(ARXFLAGS) $(MAINSLIBRARY) rlmgenkeys.o
	$(LD) $(LDFLAGS) -o rlmgenkeys rlmgenkeys.o $(LIBRARY) $(XTRALIB)
	rm -f rlmgenkeys.o

rlmclient:	rlmclient.o $(LIBRARY)
	$(LD) $(LDFLAGS) -o rlmclient rlmclient.o $(LIBRARY) $(XTRALIB)
	
rlmsign:	$(LIBRARY) $(MAINSLIBRARY)
	ar $(ARXFLAGS) $(MAINSLIBRARY) rlmsign.o
	$(LD) $(LDFLAGS) -o rlmsign rlmsign.o $(LIBRARY) $(XTRALIB)
	rm -f rlmsign.o
	
rlmverify:	$(LIBRARY) $(MAINSLIBRARY)
	ar $(ARXFLAGS) $(MAINSLIBRARY) rlmverify.o
	$(LD) $(LDFLAGS) -o rlmverify rlmverify.o $(LIBRARY) $(XTRALIB)
	rm -f rlmverify.o

roam_example:	roam_example.o $(LIBRARY)
	$(LD) $(LDFLAGS) -o roam_example roam_example.o $(LIBRARY) $(XTRALIB)
	
$(ISV).set:	$(LIBRARY) rlmsign
	./rlmsign -settings
	
$(ISV):	$(ISV).nostrip $(ISV).strip

$(ISV).nostrip:	$(LIBRARY) $(MAINSLIBRARY) $(ISV_XFER_OBJ) $(ISV_XFER_LIBS)
	ar $(ARXFLAGS) $(MAINSLIBRARY) isv_main.o
	$(LD) $(LDFLAGS) -o $(ISV) isv_main.o $(ISV_XFER_OBJ) $(ISV_XFER_LIBS) \
		$(LIBRARY) $(XTRALIB)
	rm -f isv_main.o

$(ISV).strip:
	strip $(STRIPFLAGS) $(ISV)
	
shared:	$(SO)

# The following line defines the library necessary for building the shared
# object to include the rlmid1 dongle support software on linux.  If you are 
# using rlmid1 dongles uncomment the following line (linux only).
#
#RLMID1LIB = rlmid1.a

$(SO):	$(LIBRARY)
	rm -rf tmp
	mkdir tmp; \
	cd tmp; \
	mkdir r; \
	mkdir o;\
	cp ../$(LIBRARY) .; \
	ar $(ARXFLAGS) $(LIBRARY); \
	mv rlm_*.o r; \
	mv *.o o; \
	rm -f r/rlm_privkey.o; \
	rm -f r/rlm_genlic.o; \
	rm -f r/rlm_act_doit.o; \
	rm -f r/rlmgenkeys.o; \
	rm -f r/rlmsign.o; \
	rm -f r/rlm_sign_license.o; \
	rm -f r/rlm_sign_lf.o; \
	rm -f r/rlmverify.o; \
	cd r; rm -f $(RLMWEBOBJS); cd ..; \
	cd o; rm -f $(WEBOBJS); cd ..; \
	ar $(ARFLAGS) o.a o/*.o; \
	cd .. 
	$(JAVALD) -o $(SO) $(SHAREDFLAG) $(LDFLAGS) tmp/r/*.o tmp/o.a $(RLMID1LIB) -lc $(SHAREDXTRALIB)
	rm -rf tmp

$(OBJS):	../src/license.h

rlm_pubkey.o:	../src/rlm_pubkey.c

rlm_privkey.o:	../src/rlm_privkey.c

rlmstat:
	make links

links:
	rm -f $(UTILEXECS)
	ln -s rlmutil rlmdebug
	ln -s rlmutil rlmdown
	ln -s rlmutil rlmhostid
	ln -s rlmutil rlmnewlog
	ln -s rlmutil rlmremove
	ln -s rlmutil rlmreread
	ln -s rlmutil rlmstat
	ln -s rlmutil rlmswitch
	ln -s rlmutil rlmswitchr

clean:;	rm -f $(OBJS) $(KEYOBJS) rlmsign.o $(EXECS) $(ISV) $(UTILEXECS) $(SO) core $(ISV_XFER_OBJ) actpro_demo.o
