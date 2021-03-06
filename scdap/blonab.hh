*mlist
*comdeck blonab
c
c  blonab contains cladding deformation i/o.
c
c  Cognizant engineer: dlh.
c
*if def,selap
c
c  Common block bloona contains far1 and balon2 primary i/o variables
c  and balon2 flags required for restart.
       common /bloona/ pdrato,tcebal,rnbnt,farbal(2*ndax),
     * sdfar(2*ndax),zfarbl(2*ndax),ifbaln,kntbal,nbncal,nodprm,mcme05
       real pdrato,tcebal,rnbnt,farbal,sdfar,zfarbl
       integer ifbaln,kntbal,nbncal,nodprm,mcme05
c
c                        balon2 variables
c     tcebal = maximum circumferential strain in balloon region
c     ifbaln = 1 if balon2 predicts failure
c     kntbal = 1 if balon2 predicts pellet-clad contact
c     nbncal = 1 if balon2 has been called once
c
c                        far1 variables
c     pdrato = bundle pitch to rod diameter ratio
c     rnbnt  = ratio of balloonable cells to total cells
c     nodprm = number flow area reduction subnodes
c     farbal = flow area reduction   (m**2/m**2)
c     sdfar  = standard deviation of flow area reduction
c     zfarbl = axial location flow area reduction subnodes  (m)
c
c   common block bloonb is used to pass scalar arguments to far1
c    and balon2
       common /bloonb/ dtbal,dtobal,emwbal,fabal,htcbal,h0bal,pcbal,
     * pgbal,qbal,rmpbal,r0bal,tbkbal,tc0bal,tf0bal,tgbal,timbal,rfbal,
     * zbaln,con1,con2,tm1bal,tp1bal,ztmax,zm1bal,zp1bal,zndbal,
     * fnck1,fncn1,cwkf1,cwnf1,doxcfr,tshrda(1),aad1,acd1,ard1,
     * fap1,fcp1,frp1,dvv,nncrum,nprntb,nradsh,mcme06
       real dtbal,dtobal,emwbal,fabal,htcbal,h0bal,pcbal,pgbal,qbal,
     * rmpbal,r0bal,tbkbal,tc0bal,tf0bal,tgbal,timbal,rfbal,zbaln,con1,
     * con2,tm1bal,tp1bal,ztmax,zm1bal,zp1bal,zndbal,fnck1,fncn1,cwkf1,
     * cwnf1,doxcfr,tshrda,aad1,acd1,ard1,fap1,fcp1,frp1,dvv
       integer nprntb,nradsh,nncrum,mcme06
c
c                        balon2 variables
c     fcp1    = volume weighted average cosine of the angle between
c                cladding basal poles and the tangential direction
c     frp1    = volume weighted average cosine of the angle between
c                cladding basal poles and the radial direction
c     fap1    = volume weighted average cosine of the angle between
c                cladding basal poles and the axial direction
c     acd1    = high temperature strain axisotropy coeffecient
c     aad1    = high temperature strain axisotropy coeffecient
c     ard1    = high temperature strain axisotropy coeffecient
c                see subroutine listing for relation between
c                acd, aad,ard, strain components and effective strain
c     fnck1   = effective fast fluence for strength coefficient (n/m**2)
c     fncn1   = effective fast fluence for strain hardening exponent
c                (n/m**2)
c     cwkf1   = effective cold work for strength coefficient (a0-a)/a0
c     cwnf1   = effective cold work for strain hardening exponent
c                (a0-a)/a
c     doxcfr  = average oxygen concentration in beta (wt. fraction)
c     emwbal  = heat generated by cladding oxidation  (w/m)
c       (see balon2 comments for description of balon2 variables)
c
c                        far1 variables
c     tm1bal = clad temperature at lower node (k)
c     tp1bal = clad temperature at upper node (k)
c     ztmax  = elevation of ballooning node (m)
c     zm1bal = elevation of lower node (m)
c     zp1bal = elevation of upper node (m)
c     zndbal = total length of fuel rod (m)
*endif
