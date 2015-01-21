'''
Created on 2014-11-15

@author: zhanganam
'''
from __future__ import division 
import numpy as np
#from sympy import sin,cos,tan,atan2
from math import sin, cos, pi,tan,atan2
#from math import pi
class Camera:
    '''
    class Camera
    '''

    def __init__(self, x, y, h, pan, tilt, ccd_w, ccd_h, f):
        '''
        Constructor
        x,y,h - the location of the camera , meter as the unit
        pan - the angle from the North to the vision-line , radian: 0 ~ 2*PI
        tilt - the angle DOWN from the Horizon , when UP the value should be Negative , radian:-0.5*PI ~ 0.5*PI
        ccd_w , ccd_h - the width and height of the CCD/COMS sensor . mm , cm , in or px as the unit
        f - Focal length , the same unit of ccd_w and ccd_h
        '''
        self.location = [x, y, h]
        self.pan = pan
        self.tilt = tilt
        self.ccd_w = ccd_w
        self.ccd_h = ccd_h
        self.f = f 
        
    def FOVstatic(self,maxLength=-1):
        max_ccd_h = self.ccd_h / 2.0
        if maxLength > 0:
            max_ccd_h_tmp = tan(self.tilt - atan2(self.location[2],maxLength)) * self.f
            if max_ccd_h_tmp < max_ccd_h:
                max_ccd_h = max_ccd_h_tmp
        rays = np.array([[-self.ccd_w/2.0 , -self.ccd_h/2.0 , self.f],
                [self.ccd_w/2.0 , -self.ccd_h/2.0 , self.f],
                [self.ccd_w/2.0 , max_ccd_h , self.f],
                [-self.ccd_w/2.0 , max_ccd_h , self.f]])
        points = [self.__rayTOGround(self.__rayRotate(ray)) for ray in rays]
        points.append(points[0]) #close the polygon
        return np.array(points)
    
    def Map(self,x,y):
        ray = np.array([x,y,self.f])
        return self.__rayTOGround(self.__rayRotate(ray))
    
    def __rayRotate(self, ray):
        '''
        private function rayRotate
        ray - numpy 3-member vector
        '''
        return np.array(
                        [sin(self.pan) * (sin(self.tilt) * ray[1] + cos(self.tilt) * ray[2]) + cos(self.pan) * ray[0],
                         cos(self.pan) * (sin(self.tilt) * ray[1] + cos(self.tilt) * ray[2]) - sin(self.pan) * ray[0],
                         cos(self.tilt) * ray[1] - sin(self.tilt) * ray[2]])

    def __rayTOGround(self, ray):
        '''
        priavte function rayTOGround
        ray - numpy 3-member vector
        '''
        return np.array(self.location[0:2]) - self.location[2] / ray[2] * ray[0:2]
        
if __name__ == "__main__":
    import matplotlib
    import matplotlib.pyplot as plt
    ccd_w = 3264.0
    ccd_h = 2448.0
    c = Camera(680250.528, 3555005.36, 13.85, (180+12.832782279588892)/180.0 * pi ,(36.0)/180.0 * pi, ccd_w, ccd_h, 2056.063)
    #c = Camera(680248.560, 3555004.72, 13.00, (79.6103)/180.0 * pi ,29.0/180.0 * pi, ccd_w, ccd_h, 2056.063)
    fg = plt.figure()
    ax = fg.add_subplot(111)
    ax.axis('equal')
    '''for p in range(0,1):
        for t in range(31,32):
            c.pan = p/180.0 * pi
            c.tilt = t/180.0 * pi
            polygon = c.FOVstatic()
            print(polygon)
            print(c.Map(ccd_w/2,ccd_h/2))
            ax.fill(polygon[:,0],polygon[:,1],'b')
    '''
    polygon1 = c.FOVstatic()
    polygon2 = c.FOVstatic(50.0)
    ax.fill(polygon1[:,0],polygon1[:,1],'b')
    ax.fill(polygon2[:,0],polygon2[:,1],'r')
    plt.show()
