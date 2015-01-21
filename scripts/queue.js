/********************************************************************************************************
queue.js
模拟队列类
created by liuyang
2015/01/11
*********************************************************************************************************/
//
// 循环队列
function CycleQueue() {
  this.base = {};
  this.front = this.rear = 0;
  this.MAXQSIZE = 4;
}

CycleQueue.prototype = {
  enQueue: function (data) {
    //if ((this.rear + 1) % this.MAXQSIZE === 0) throw new Error('cycleQueue is already full!');

    this.base[this.rear] = data;
    this.rear = (this.rear + 1) % this.MAXQSIZE;
  },
  deQueue: function () {
    if (this.front === this.rear) throw new Error('cycleQueue is already empty');

    var elem = this.base[this.front];
    this.front = (this.front + 1) % this.MAXQSIZE;

    return elem;
  },
  clear: function () {
    this.base = {};
    this.front = this.rear = 0;
  },
  size: function () {
    return (this.rear - this.front + this.MAXQSIZE) % this.MAXQSIZE;
  },
  getHead: function () {
    var elem = this.base[this.front];
    return elem ? elem : null;
  },
};