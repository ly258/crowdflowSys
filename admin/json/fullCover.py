#!/usr/bin/env python
# coding=utf-8
from __future__ import division
import Polygon, Polygon.IO
import CameraCoverage.camera as cc
import math
import sys
import getopt
import psycopg2 as pg
id = 'null'
try:
#if 1==1:
    opts , args = getopt.getopt(sys.argv[1:],'id',['id='])
    for o,a in opts:
        if o in ('-id','--id'):
            id = a
    if id == 'null':
        print("input id!")
        raise ValueError
    conn = pg.connect(database="videocms",user="postgres",password="postgres",host="127.0.0.1",port="5432")
    #print(conn)
    cur = conn.cursor()
    cur.execute("select id as c0 , type as c1,ST_AsText(location) as c2,height as c3,ccd_width as c4,ccd_height as c5,pan as c6,tilt as c7,focal as c8 from videocms_camera where id='"+id+"'")
    #print(cur.rowcount)
    if cur.rowcount==0:
        print("select id as c0 , type as c1,ST_AsText(location) as c2,height as c3,ccd_width as c4,ccd_height as c5,pan as c6,tilt as c7,focal as c8 from videocms_camera where id='"+id+"'")
        print(0)
        print("no id found")
        exit(0)
    row = cur.fetchone()
    #print(row)
    location_str = row[2].split(' ')
    c = cc.Camera(float(location_str[0].split('(')[1]),float(location_str[1].split(')')[0]),float(row[3]),0,0,float(row[4]),float(row[5]),float(row[8]))
    if row[1] == 0: #stable camera
        p_f = row[6]
        p_t = row[6]
        t_f = row[7]
        t_t = row[7]
    elif row[1] == 1 or row[1] == 2: #FOV or full-size CameraCoverage
        cur.execute("select max_tilt , min_tilt,max_rota,min_rota from videocms_cameraadjust where id = %s",(id,))
        if cur.rowcount == 0:
            print(0)
            print("adjust para no found")
            exit(0)
        para = cur.fetchone()
        p_f = para[3]
        p_t = para[2]
        t_f = para[1]
        t_t = para[0]

    #print([t_f,t_t,p_f,p_t])
    #polygon = Polygon()
    fullCoverFov = []
    #c.pan = p_f*math.pi/180.0
    index = 0
    for t in range(int(t_f),int(t_t+1)):
        #print(t)
        c.tilt = t*math.pi/180.0
        fov = c.FOVstatic(120)
        fovPolygon = Polygon.Polygon(fov,120)
        if index!=0: 
            fullCoverFov = fullCoverFov + fovPolygon
        else:
            fullCoverFov = fovPolygon
        index +=1
    sliceFov = Polygon.Polygon(fullCoverFov)
    #print("s")
    #print(sliceFov)
    index = 0
    for p in range(int(p_f),int(p_t+1)):
        #print(float(p)*math.pi/180.0)
        p_sliceFov = Polygon.Polygon(sliceFov)
        p_sliceFov.rotate(-float(p)*math.pi/180.0,c.location[0],c.location[1])
        if index==0:
            fullCoverFov = p_sliceFov
        else:
            fullCoverFov = fullCoverFov + p_sliceFov
        index += 1

    #print("f")
    #print(fullCoverFov)
    polygon = Polygon.Polygon(fullCoverFov)
    #polygon = Polygon.Polygon(sliceFov)

    cur.execute("select * from videocms_fov where id=%s",(id,))
    #print(cur.rowcount)
    if(cur.rowcount==0):
        cur.execute("insert into videocms_fov(id) values (%s)",(id,))

    sql = "ST_GeometryFromText('POLYGON("
    index = 0
    for c in polygon:
        if index>0:
            sql = sql + ","
        sql = sql + ("(%.5f %.5f" % (c[0][0],c[0][1]))
        for a,b in c[1:]:
            sql = sql + (",%.5f %.5f" % (a,b))
        sql = sql + (",%.5f %.5f)" % (c[0][0],c[0][1]))
        index = index+1
    sql = sql + ")')"

    cur.execute("update videocms_fov set fov_real = "+sql+" , fov_full="+sql+" where id='"+id+"'")
    #print("update videocms_fov set fov_real = "+sql+" , fov_full="+sql+" where id='"+id+"'")
    conn.commit()
    print(cur.rowcount)
    conn.close()
except:
    print(0)
    print("exception")
else:
    pass
