# w7Laravel

#### 使用说明

```
# 1 声明
CacheResolver::setCacheResolver(Cache::store());

class Clazz extend Model {

}

Clazz::flush();
Clazz::find($idOrIds);
Clazz::query()->insertGetId();
$clazz->update();
$clazz->save();
$clazz->delete();

```