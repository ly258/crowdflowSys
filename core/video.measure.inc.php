<?php
class measure
{
    public $planeX;//图像点的参考平面X坐标
    public $planeY;//图像点的参考平面Y坐标
    public $GeoX;//图像点的地理坐标X
    public $GeoY;//图像点的地理坐标Y
    //图像点的像素坐标
    public $startX;
    public $startY;
    public $endX;
    public $endY;
    
    /*----------------------------
     * 功能 : 空间位置计算（图像上任一像素点p(x，y)空间位置的计算）
     *----------------------------
     * 函数 : SpacePosCal
     * 访问 : public
     * 返回 : 图像点的参考平面坐标：planeX，planeY 或者地理坐标：Xw，Yw
     *
     * 参数 : focal		 相机焦距
     * 参数 : H	    	 相机相对于参考平面的距离
     * 参数 : SIt         相机光轴与基线之间的夹角
     * 参数 : px,py       像素点坐标
     */
    public function SpacePosCal($IsGeographic,$px,$py,$focal,$ccd_width,$ccd_height,$height,$SIt,$Xc,$Yc,$Hc,$a)
    {
        //反算像素坐标
        $i=480/$ccd_width;
        $j=320/$ccd_height;
        $originPx=$px/$i;
        $originPy=$py/$j;
        //计算摄像机视域角
        $FOVv=2*atan($ccd_height/(2*$focal));
        $FOVh=2*atan($ccd_width/(2*$focal));
        //计算垂直角和旋转角ψ和φ
        $Fai=$SIt+($ccd_height/2-$originPy)*($FOVv/$ccd_height);
        $SiMa=($originPx-$ccd_width/2)*($FOVh/$ccd_width);
        //计算点p(x,y)的参考平面坐标X，Y
        $PI=pi();
        $this->planeY=$height*tan($Fai*$PI);
        $this->planeX=$this->planeY*tan($SiMa*$PI); 
        //转换为地理坐标（Xw,Yw）
        if($IsGeographic)
        {
            $SS=sqrt($this->planeX*$this->planeX+$this->planeY*$this->planeY);
            $this->GeoX=$Xc+$SS*cos($a+$SiMa);
            $this->GeoY=$Yc+$SS*sin($a+$SiMa);
        }      
    }
    
    /*----------------------------
     * 功能 : 物体高度量测（在图像上选择物体的顶点tp(x,y)，由顶点拉一条与地面垂直的直线确定物体的
     *        底点bp(x,y)，计算物体的高度Hobj）
     *----------------------------
     * 函数 : HeightCal
     * 访问 : public
     * 返回 : 物体高度计算值：Hobj
     *
     * 参数 : FOVv		相机垂直视域角
     * 参数 : FOVh		相机水平视域角
     * 参数 : H	    	相机相对于参考平面的距离
     * 参数 : θ  SIt      相机光轴与基线之间的夹角
     */
    public function HeightCal($IsSpace,$tpx2,$tpy2,$bpx1,$bpy1,$bpx,$bpy,$focal,$ccd_width,$ccd_height,$height,$SIt,$Xc,$Yc,$Hc,$a)
    {
        //计算底点和顶点的参考平面坐标（X1,Y1)、(X2,Y2)
        $this->SpacePosCal(false, $tpx2, $tpy2, $focal, $ccd_width, $ccd_height, $height, $SIt, $Xc, $Yc, $Hc, $a);
        $X2=$this->planeX;
        $Y2=$this->planeY;
        $this->SpacePosCal(false, $bpx1, $bpy1, $focal, $ccd_width, $ccd_height, $height, $SIt, $Xc, $Yc, $Hc, $a);
        $X1=$this->planeX;
        $Y1=$this->planeY;
        //计算物体高度
        if($IsSpace==true)
        {
            /*在图像上选择底点坐标bpx,bpy*/
            $this->SpacePosCal(false, $bpx, $bpy, $focal, $ccd_width, $ccd_height, $height, $SIt, $Xc, $Yc, $Hc, $a);
            $BX=$this->planeX;
            $BY=$this->planeY;
            $H1=$height*(($Y1-$BY)/$Y1);
            $H2=$height*(($Y2-$BY)/$Y2);
            $Hobj=abs($H1-$H2);
        }else 
        {
            //计算物体高度
            $Hobj=$height*(($Y2-$Y1)/$Y2);
        }
        return $Hobj;
    }
    
    /*----------------------------
     * 功能 : 两点基平面距离量测（在图像上选择需量测的两个图像点p1(x1,y1)和p2(x2,y2)，计算两点间的基平面距离）
     *
     *----------------------------
     * 函数 : DistanceCal
     * 访问 : public
     * 返回 : 两点间距离计算值：Dp
     *
     * 参数 : FOVv		相机垂直视域角
     * 参数 : FOVh		相机水平视域角
     * 参数 : H	    	相机相对于参考平面的距离
     * 参数 : θ  SIt      相机光轴与基线之间的夹角
     */
    public function DistanceCal($px1,$py1,$px2,$py2,$focal,$ccd_width,$ccd_height,$height,$SIt,$Xc,$Yc,$Hc,$a)
    {
        //计算底点和顶点的参考平面坐标（X1,Y1)、(X2,Y2)
        $this->SpacePosCal(false, $px2, $py2, $focal, $ccd_width, $ccd_height, $height, $SIt, $Xc, $Yc, $Hc, $a);
        $X2=$this->planeX;
        $Y2=$this->planeY;
        $this->SpacePosCal(false, $px1, $py1, $focal, $ccd_width, $ccd_height, $height, $SIt, $Xc, $Yc, $Hc, $a);
        $X1=$this->planeX;
        $Y1=$this->planeY;
        //计算两点间距离
        $Dp=sqrt(($X1-$X2)*($X1-$X2)+($Y1-$Y2)*($Y1-$Y2));
        return $Dp;
    }
    
    /*----------------------------
     * 功能 : 两点空间距离量测（在图像上选择需量测的两个图像点p1(x1,y1)和p2(x2,y2)，计算两点间的实际距离）
     *
     *----------------------------
     * 函数 : SpaceDistanceCal
     * 访问 : public
     * 返回 : 两点间距离计算值：Dp
     *
     * 参数 : FOVv		相机垂直视域角
     * 参数 : FOVh		相机水平视域角
     * 参数 : H	    	相机相对于参考平面的距离
     * 参数 : θ  SIt      相机光轴与基线之间的夹角
     */
    public function SpaceDistanceCal($tpx1,$tpy1,$bpx1,$bpy1,$tpx2,$tpy2,$bpx2,$bpy2,$focal,$ccd_width,$ccd_height,$height,$SIt,$Xc,$Yc,$Hc,$a)
    {
        $this->SpacePosCal(false, $tpx1, $tpy1, $focal, $ccd_width, $ccd_height, $height, $SIt, $Xc, $Yc, $Hc, $a);
        $TX1=$this->planeX;
        $TY1=$this->planeY;
        $this->SpacePosCal(false, $tpx2, $tpy2, $focal, $ccd_width, $ccd_height, $height, $SIt, $Xc, $Yc, $Hc, $a);
        $TX2=$this->planeX;
        $TY2=$this->planeY;
        //计算两点相对于参考面的高度
        $H1=$this->HeightCal(false,$tpx1,$tpy1,$bpx1,$bpy1,0,0,$focal,$ccd_width,$ccd_height,$height,$SIt,$Xc,$Yc,$Hc,$a);
        $H2=$this->HeightCal(false,$tpx2,$tpy2,$bpx2,$bpy2,0,0,$focal,$ccd_width,$ccd_height,$height,$SIt,$Xc,$Yc,$Hc,$a);
        //计算两点的实际距离
        $Ds=sqrt(($H1-$H2)*($H1-$H2)+($TX1-$TX2)*($TX1-$TX2)+($TY1-$TY2)*($TY1-$TY2));
        return $Ds;
    }
    
    /**
     * 读取视频量测所需要的参数
     * @param 数据库连接 $link
     * @param 视频当前帧时间 $currenttime
     * @return 视频量测所需要的参数 <multitype:, multitype:>
     */
    public function obtainparameter($link,$para)
    {
        $sql="select focal,tilt,height from videocms_videostate where (abs(time-".$para['curtime'].")=(select min(abs(time-".$para['curtime'].")) from videocms_videostate)) and vid='".$para['vid']."'";
        $rows=fetchOne($link, $sql);
        return $rows;
    }
    
    /**
     * 视频量测
     * @param 量测类型 $act
     * @param 量测参数 $para
     * @param 量测参数 $rows
     * @return 量测结果 number
     */
    public function calculate($act,$para,$rows,$coordinate)
    {
        switch ($act)
        {
            case "SpacePosCal":
                if($coordinate['isgeographic']==0)
                {
                    $this->SpacePosCal(false, $para['x'], $para['y'], $rows['focal'], $para['ccd_width'], $para['ccd_height'], $rows['height'], $rows['tilt'], $coordinate['xc'], $coordinate['yc'], $coordinate['hc'], $coordinate['a']);
                }else {
                    $this->SpacePosCal(true, $para['x'], $para['y'], $rows['focal'], $para['ccd_width'], $para['ccd_height'], $rows['height'], $rows['tilt'], $coordinate['xc'], $coordinate['yc'], $coordinate['hc'], $coordinate['a']);
                }
                
                $planeX=$this->planeX;
                $planeY=$this->planeY;
                $coor["coorx"]=round($planeX,2);
                $coor["coory"]=round($planeY,2);
                return $coor;
                break;
                
            case "HeightCal":
                $result=$this->HeightCal(false, $para['tpx2'], $para['tpy2'], $para['bpx1'], $para['bpy1'], 0, 0, $rows['focal'], $para['ccd_width'], $para['ccd_height'], $rows['height'], $rows['tilt'], $coordinate['xc'], $coordinate['yc'], $coordinate['hc'], $coordinate['a']);
                $hobj=round($result,2);
                return $hobj;
                break;
                
            case "SpaceHeiCal":
                $result=$this->HeightCal(false, $para['tpx2'], $para['tpy2'], $para['bpx1'], $para['bpy1'], $para['bpx'], $para['bpy'], $rows['focal'], $para['ccd_width'], $para['ccd_height'], $rows['height'], $rows['tilt'], $coordinate['xc'], $coordinate['yc'], $coordinate['hc'], $coordinate['a']);
                $spacehei=round($result,2);
                return $spacehei;
                break;
                
            case "DistanceCal":
                $result=$this->DistanceCal($para['px1'], $para['py1'], $para['px2'], $para['py2'], $rows['focal'], $para['ccd_width'], $para['ccd_height'], $rows['height'], $rows['tilt'], $coordinate['xc'], $coordinate['yc'], $coordinate['hc'], $coordinate['a']);
                $dist=round($result,2);
                return $dist;
                break;
                
            case "SpaceDistanceCal":
                $result=$this->SpaceDistanceCal($para['tpx1'], $para['tpy1'], $para['bpx1'], $para['bpy1'], $para['tpx2'], $para['tpy2'], $para['bpx2'], $para['bpy2'],  $rows['focal'], $para['ccd_width'], $para['ccd_height'], $rows['height'], $rows['tilt'], $coordinate['xc'], $coordinate['yc'], $coordinate['hc'], $coordinate['a']);
                $spacedist=round($result,2);
                return $spacedist;
                break;
        }
    }
}