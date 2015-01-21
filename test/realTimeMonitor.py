#coding:utf-8
import psycopg2
import random
import time 
import json

cameraIDs = ["32040451991311000096","32040451991311000098","32040451991311000099","32040451991311000100","32040451991311000101"] 
#连接数据库
conn= psycopg2.connect(host="localhost",port="5432",
                       user="postgres",password="lysc16978",
                       dbname="realtime_crowd")
#最大随机数
maxNum = 30
sleepTime = 2
cur = conn.cursor()
while True: 
    people = [];
    for id in cameraIDs:          
        randNum = random.randint(0,maxNum)
        #print(randNum) 
        cur.execute("SELECT ccd_width,ccd_height FROM videocms_camera WHERE id='"+id+"'")   
        row = cur.fetchone()   
        width=row[0]
        height=row[1]
        obj = {"id":id,"num":randNum,"people":[]};
        for i in range(randNum):
            x = random.randint(0,width)
            y = random.randint(0,height)
            #print("%d,%d" %(x,y))
            obj["people"].append({"x":x,"y":y});
            
        people.append(obj);              
    time.sleep(sleepTime)
    encodedjson = json.dumps(people)  
    cur.execute("INSERT INTO \"rt_peopleCount\"(people,time) VALUES(%s,%s)",(encodedjson,int(time.time())));
    conn.commit()
    print time.time()
    print encodedjson
cur.close()          
conn.close() 