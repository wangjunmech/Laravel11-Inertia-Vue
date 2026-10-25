<template>
    <div>        
        <div class="text-lg font-semibold">常用国内外模具钢材品牌型号</div>
<!-- 点击打开弹窗案例 -->
  <button class="btn bg-zinc-200" @click="showModal = true">钢料基础知识及常见钢料牌号对照</button>
  <!-- 弹窗遮罩层 -->
<!-- 遮罩层 mask -->
<div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-[999]" @click.self="showModal = false">
  <!-- 弹窗主体：宽600px，高520px，flex纵向排布，用来做固定头尾+滚动内容 -->
  <div class="bg-white w-4/5 h-[520px] rounded-lg flex flex-col p-4">
    <!-- 头部 -->
    <div class="flex justify-between items-center mb-3">
      <h3 class="text-lg font-medium">模具钢相关知识</h3>
      <span class="cursor-pointer text-xl">
        <a 
          href="https://static2.xunxiang.site/uploads/sites/1098/2024/12/ca31d045aacc5dbe4f9b6d7f9b568d39.jpg" 
          target="_blank"
          class="text-blue-500 hover:underline"
        >
          查看钢材制作流程图片
        </a>
      </span>
    </div>

    <!-- 内容区：flex-1占剩余高度，overflow-y-auto 超出自动出滚动条 -->
    <div class="flex-1 overflow-y-auto">
      弹窗内容区域
      <div>
        <table style="width: 99.4659%;">
    <tbody>
    <tr>
    <td class="border border-gray-300 px-3 py-2">GRADE</td>
    <td class="border border-gray-300 px-3 py-2">AISI</td>
    <td class="border border-gray-300 px-3 py-2">DIN</td>
    <td class="border border-gray-300 px-3 py-2">DESCRIPTION</td>
    </tr>
    <tr>

    <td class="border border-gray-300 px-3 py-2">HolderBloc®</td>
    <td class="border border-gray-300 px-3 py-2">4140+S</td>
    <td class="border border-gray-300 px-3 py-2">1.2331</td>
    <td class="border border-gray-300 px-3 py-2">Resulfurized AISI 4140 modified for excellent machinability. Supplied in the heat-treated condition, approximately 27-33 Rockwell C on surface. Generally suited for applications such as mold bases where there is extensive machining. Not recommended for highly polished surfaces (Diamond paste) where sulfides may be revealed as fine, directional scratches. Supplied pre-hardened to 27-33 HRC surface. Due to low hardenability, it is recommended to use COREBLOC™ for holders or core blocks larger than 14″ thick.</td>
    </tr>
    <tr>

    <td class="border border-gray-300 px-3 py-2">CoreBloc™</td>
    <td class="border border-gray-300 px-3 py-2">P20 Mod</td>
    <td class="border border-gray-300 px-3 py-2">1.2312</td>
    <td class="border border-gray-300 px-3 py-2">COREBLOC™ is a new patented prehardened mold steel grade specially designed for increased through hardenability when compared to 4140 or HOLDERBLOC™. Material is easier to machine due to the addition of sulfur and suitable for holder and core block sizes from 14″ up to 30″. For larger holders or core blocks, we recommend use of MD™.</td>
    </tr>
    <tr style="height: 96px;">
    <td class="border border-gray-300 px-3 py-2">MAR-X®</td>
    <td class="border border-gray-300 px-3 py-2">15-5 PH</td>
    <td class="border border-gray-300 px-3 py-2">–</td>
    <td class="border border-gray-300 px-3 py-2">AISI 15-5 PH (15% Cr + 5% Ni) precipitation hardening, VAR remelted stainless steel. Excellent corrosion resistance for both atmospheric and chemical attack. The heat response characteristics of this grade provide especially good welding characteristics. Absence of hardening in the weld Heat Affected Zone (HAZ) offers improved blending characteristics and reduced cracking potential in welded areas.</td>
    </tr>
    <tr>

    <td class="border border-gray-300 px-3 py-2">Mold Die®</td>
    <td class="border border-gray-300 px-3 py-2">P20+Ni</td>
    <td class="border border-gray-300 px-3 py-2">1.2738</td>
    <td class="border border-gray-300 px-3 py-2">An optimized AISI P20. Most widely used alloy for plastic molding. Supplied pre-hardened to 285-321 BHN (30-34 HRC). Mold Die is the P20 reference around the world.</td>
    </tr>
    <tr>
    <td class="border border-gray-300 px-3 py-2">Hi-Hard MD®</td>
    <td class="border border-gray-300 px-3 py-2">P20+Ni HH</td>
    <td class="border border-gray-300 px-3 py-2">1.2738 HH</td>
    <td class="border border-gray-300 px-3 py-2">Hi-Hard P20 is heat-treated to a higher hardness than standard P20 and carefully formulated to enhance the performance of the steel. This mold steel is sold pre-hardened at 321-352 BHN contributing to improved wear resistance and polishability. The chemical composition in conjunction with special processing steps creates the best deep hardening plastic mold steel available.</td>
    </tr>
    <tr>

    <td class="border border-gray-300 px-3 py-2">Mold Die®-Xtra</td>
    <td class="border border-gray-300 px-3 py-2">P20 Mod</td>
    <td class="border border-gray-300 px-3 py-2">1.2738 mod</td>
    <td class="border border-gray-300 px-3 py-2">A modified AISI P20 improved to increase thermal conductivity, through harden ability and ease of machining &amp; texturing. The new standard alloy for plastic molding. Supplied pre-hardened to 285-321 BHN (30-34 HRC), 321-352 BHN (34-38 HRC), or 363-401 BHN (39-43 HRC). MD® Xtra provides the most homogeneous structure available, giving maximum machinability and polish ability in the industry.</td>
    </tr>
    <tr>

    <td class="border border-gray-300 px-3 py-2">MLQ®-Xtra</td>
    <td class="border border-gray-300 px-3 py-2">P20 Mod HH (Remelt)</td>
    <td class="border border-gray-300 px-3 py-2">1.2738 remelt</td>
    <td class="border border-gray-300 px-3 py-2">Remelted for the highest surface quality applications, MLQ®-Xtra is designed for through-hardenability, ease of machining and simple post-production mold maintenance. It has high impact strength, excellent temper resistance and high thermoconductivity values. The well-balanced chemistry assures homoegenous hardness and guaranteed polishability to SPI A1 finishes.</td>
    </tr>
    <tr>

    <td class="border border-gray-300 px-3 py-2">420M®</td>
    <td class="border border-gray-300 px-3 py-2">420 Mod</td>
    <td class="border border-gray-300 px-3 py-2">1.2083</td>
    <td class="border border-gray-300 px-3 py-2">420M® is a martensitic stainless steel grade optimized for mold steel applications. It is specially designed for wear resistance, polishability, improved corrosion resistance, and simple post-production mold maintenance. 420M® is available in standard and remelt quality.</td>
    </tr>
    <tr>
    <td class="border border-gray-300 px-3 py-2">420UQ ™</td>
    <td class="border border-gray-300 px-3 py-2">420 ESR + VAR</td>
    <td class="border border-gray-300 px-3 py-2">1.2083</td>
    <td class="border border-gray-300 px-3 py-2">When optical quality polish is required, the ESR-VAR double remelt process guarantees a pristine surface.</td>
    </tr>
    <tr>

    <td class="border border-gray-300 px-3 py-2">DRX®</td>
    <td class="border border-gray-300 px-3 py-2">S7</td>
    <td class="border border-gray-300 px-3 py-2">1.2357</td>
    <td class="border border-gray-300 px-3 py-2">Mold quality VAD (Vacuum Arc Degassed) alloy with good wear resistance, toughness and polishability. Higher carbon and alloy (3.25% Cr, 1.4% Mo) for emphasis on good wear resistance. Typically used for inserts or smaller molds in applications requiring high production volumes or molds subjected to high-wear conditions</td>
    </tr>
    <tr>

    <td class="border border-gray-300 px-3 py-2">DC®</td>
    <td class="border border-gray-300 px-3 py-2">H13</td>
    <td class="border border-gray-300 px-3 py-2">1.2344</td>
    <td class="border border-gray-300 px-3 py-2">Die Cavity has excellent abrasion resistance due to high vanadium content that forms extremely hard vanadium carbide particles. Excellent wear resistance at higher temperatures.</td>
    </tr>
    <tr>
    <td class="border border-gray-300 px-3 py-2">DC®Q</td>
    <td class="border border-gray-300 px-3 py-2">H13 (Remelt)</td>
    <td class="border border-gray-300 px-3 py-2">1.2344</td>
    <td class="border border-gray-300 px-3 py-2">Premium polishability DC® with the same excellent abrasion resistance, but SPI A1 surface finish guarantee.</td>
    </tr>
    <tr style="height: 76px;">

    <td class="border border-gray-300 px-3 py-2">SUPERDIE™</td>
    <td class="border border-gray-300 px-3 py-2"></td>
    <td class="border border-gray-300 px-3 py-2">1.2367 mod</td>
    <td class="border border-gray-300 px-3 py-2">SUPERDIE exhibits higher strength and surface hardness at room temperature than H13 and H11 type alloys when tempered at identical tempering temps. Hardnesses typically in the 42 to 52 HRC with standard hardening procedures.</td>
    </tr>
    </tbody>
</table>
      </div>
    </div>

    <!-- 底部按钮 -->
    <div class="mt-4 flex justify-end gap-2">
      <button @click="showModal = false" class="px-3 py-1 border rounded">取消</button>
      <button @click="handleOk" class="px-3 py-1 bg-blue-500 text-white rounded">确定</button>
    </div>
  </div>
</div>
        <div>ESR = 电渣重熔精炼版本,同牌号高纯净度升级版</div>
        <div v-for="item in moldSteelBrandData" :key="item.id" class="border-2 border-dashed border-red-500 p-2 m-2 rounded-lg">
            <a :href="item.website" target="_blank"
            class="bg-green-300 p-2 rounded-lg text-lg hover:bg-blue-400 hover:text-white"
            >{{ item.name }}<span>----{{ item.country }}</span></a>
           
            <div class="bg-red-100 mt-2">{{ item.remark }}</div>
            <div class="flex flex-wrap gap-1">
                <div class="flex w-fit px-1 bg-gray-400 m-1 rounded-sm whitespace-nowrap" v-for="coldwork in item.coldwork" :key="coldwork">{{ coldwork }}</div>
                <div class="flex w-fit px-1 bg-red-400 m-1 rounded-sm whitespace-nowrap" v-for="hotwork in item.hotwork" :key="hotwork">{{ hotwork }}</div>
                <div class="flex w-fit px-1 bg-yellow-400 m-1 rounded-sm whitespace-nowrap" v-for="plasticmold in item.plasticmold" :key="plasticmold">{{ plasticmold }}</div>
                <div class="flex w-fit px-1 bg-blue-200 m-1 rounded-sm whitespace-nowrap" v-for="others in item.others" :key="others">{{ others }}</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

//弹窗显示控制
const showModal = ref(false)
const handleOk = () => {
  console.log('确认')
  showModal.value = false
}

// 模具钢材品牌数据源
const moldSteelBrandData = [
  {
    id: 1,
    name: "一胜百 ASSAB(Uddeholm)",
    country: "瑞典",
    website: "https://www.assab-china.com/china/zh-hans/home/",
    remark: "voestalpine集团；代表牌号S136、718、8407、Vanadis粉末钢",
    coldwork: ["ASSAB 88","ASSAB M2","ASSAB PM 23 SUPERCLEAN","ASSAB PM 30 SUPERCLEAN","ASSAB PM 60 SUPERCLEAN","ASSAB XW-42","CALDIE","CALMAX","UNIMAX","VANADIS 4 EXTRA SUPERCLEAN","VANADIS 8 SUPERCLEAN","VANADIS 8 XL","VANCRON SUPERCLEAN","VIKING"],
    hotwork: ['ASSAB 2344','ASSAB 8407 2M','ASSAB 8407 SUPREME','CALDIE','DIEVAR','FORMVAR','QRO 90 HT','QRO 90 SUPREME','SKOLVAR','UNIMAX','VIDAR 1 ESR','VIDAR SUPERIOR'],     
    plasticmold: ['ASSAB 2083','ASSAB 2083 ESR','ASSAB 2344','ASSAB 618 HH','ASSAB 718 HH','ASSAB 718 SUPREME','ASSAB 8407 2M','ASSAB 88','ASSAB PM 23 SUPERCLEAN','ASSAB PM 30 SUPERCLEAN','ASSAB PM 60 SUPERCLEAN','ASSAB VIMCOR','CALDIE','CALMAX','ELMAX SUPERCLEAN','IDUN','MIRRAX 40','MIRRAX ESR','NIMAX','NIMAX ESR','POLMAX','ROYALLOY','STAVAX ESR','TYRAX ESR','UNIMAX','VANADIS 4 EXTRA SUPERCLEAN','VANAX SUPERCLEAN'],
    others: ["ASSAB 17由高钴合金高速钢制硬度67-69用于车刀"],
  },
  {
    id: 2,
    name: "乌德霍姆 Uddeholm",
    country: "瑞典",
    website: "https://www.uddeholm.com/app/uploads/sites/216/productdb/api/tech_uddeholm_steel-for-moulds_en.pdf",
    remark: "原厂，与一胜百同集团，一胜百负责亚洲销售",
    coldwork: [""],
    hotwork: [""],
    plasticmold: ['Alumec 89* (~160)','Impax Supreme 33 (~310)','Ramax HH 37 (~340)','Mirrax 40 40 (~380)','Nimax ESR 40 (~380)','Nimax 40 (~380)','Corrax 36–50','Mirrax ESR 45–50','Orvar Supreme 45–52','Stavax ESR 45–52','Polmax 45–52','Vidar 1 ESR 45–52','Unimax 50–58','Tyrax ESR 55–58','Elmax 56–60','Vanadis 4 Extra','Mirrax ESR 45–50','Stavax ESR 45–52','Orvar Supreme 45–52','Unimax 52–58','Tyrax ESR 55–58','Elmax 56–60','Vanadis 4 Extra 58–62'],
    others: [""],
  },
  {
    id: 3,
    name: "博乐 BÖHLER",
    country: "奥地利",
    website: "https://www.bohler.com.cn/zh-hans/",
    remark: "voestalpine集团；代表牌号M300、M310、M380",
    coldwork: ["K100","K105","K107","K110","K137","K190 MICROCLEAN","K294 MICROCLEAN","K340 ECOSTAR","K340 ISODUR","K346","K353","K360 ISODUR","K390 MICROCLEAN","K490 MICROCLEAN","K497 MICROCLEAN","K888 MATRIX","K890 MICROCLEAN","K305","K306","K313","K320","K329","K600","K601","K605","K245","K455","K460","K720","K700"],
    hotwork: ["w300 ISOBLOC","w300 ISODISC","w302 ISOBLOC","w302 ISODISC","w505 ISODISC","w520 ISODISC","w350 ISOBLOC","w360 ISOBLOC","w400 VMR","w403 VMR","w460 VMR","w720 VMR","w751"],
    plasticmold: ["M 310 ISOPI AST","M333 ISOPI AST","M340 ISOPLAST","M368 MICROCLEAN","M380 ISOPLAST","M390 MICROCLEAN","M398 MICROCLEAN","N685","N690","N695","M300","M303","M303","M303 HIGHHARD","M303 ISOPI AST","M315","M200","M238","M238 HIGHHARD","M261"],
    others: ['L625 AMPO 用于3D打印的粉末',
'L718 AMPO 用于3D打印的粉末'],
  },
  {
    id: 4,
    name: "大同特殊钢 DAIDO",
    country: "日本",
    website: "https://www.daidosteel.com.cn/",
    remark: "代表牌号NAK80、DC53、DHA1、PX5、PX88",
    coldwork: ['DHA1','DHA-MAX','DHA-THERMO','DHA-WORLD','DH31-EX','DH2F'], 
    hotwork: ['GOA','DC11','DC53','DCMX','DRM2','DRM3','RDC145'],
    plasticmold: ['PX4','PAC5000','NAK80','S-STAR-A','PAT868S','PD613'],
    others: [""],
  },
  {
    id: 5,
    name: "日立金属(博迈立铖特殊钢,YSS安来特殊钢 )",
    country: "日本",
    website: "https://www.china.proterial.com/category/%e4%ba%a7%e5%93%81%e4%bb%8b%e7%bb%8d/%e5%b7%a5%e6%a8%a1%e5%85%b7%e9%92%a2",
    remark: "代表牌号HPM7、HPM38、HPM‑MAGIC、CENA1；国内东莞运营",
    coldwork: ["SLD-MAGIC","SLD10","SGT","YCS3","ACD37","HMD5","YXM1","YXM4","YXR7","YXR3","YXR33","HAP5R","HAP10","HAP40","HAP72"],
    hotwork: ["DAC","DAC-i","DAC-X","DAC-MAGIC","DAC55","DAC3","DAC40","YEM-K","MDC-K","FDAC","DM","YXR33"],
    plasticmold: ["HPM7","HPM38","PSL","FDAC","HPM38","HPM31","HAP5R","HPM75","YAG300"],
    others: ["YSS高速工具钢","YSS压铸模具钢"],
  },
  {
    id: 6,
    name: "葛利兹 Groeditz(GMH集团)",
    country: "德国",
    website: "https://www.groeditz.com.cn/",
    remark: "代表牌号1.2343、1.2344、XPM；塑胶、压铸模具钢",
    coldwork: ["SWG 2379","SWG 2357"],
    hotwork: ["SWG 2344 (SWG EX4)","SWG 2344 VICTORY ESR (SWG EX4 VICTORY ESR)","SWG EX1 VICTORY ESR","SWG EX6 VICTORY ESR","SWG GPM58 VICTORY ESR","SWG 2367 VICTORY ESR (SWG EX7 VICTORY ESR)"],
    plasticmold: ['SWG 2767','SWG 2357 (AISI S7)','SWG 2311 ','SWG 2312','SWG 2738','SWG 738HH','SWG XPM','SWG XPM VICTORY ESR','SWG GEST80 VICTORY ESR','SWG 2711 ','SWG 2711mod','SWG 2714','SWG CPM40 VICTORY ESR','SWG 2343 (SWG EX3)','SWG 2343 VICTORY ESR','SWG 2344 (SWG EX4)','SWG 2344 VICTORY ESR','SWG GPM58 VICTORY ESR ','SWG 2085'],
    others: [""],
  },
  {
    id: 7,
    name: "布德鲁斯 Buderus Edelstahl",
    country: "德国",
    website: "https://www.buderus-steel.com/",
    remark: "GMH Gruppe旗下，主营热作、塑胶模具钢",
    coldwork: [""],
    hotwork: [""],
    plasticmold: [""],
    others: [""],
  },
  {
    id: 8,
    name: "抚顺特钢（抚钢）",
    country: "中国",
    website: "http://www.fs‑ss.com/index.html",
    remark: "国内老牌特钢；FS136、FS718，覆盖热作、冷作、塑胶模具钢",
    coldwork: ["Cr12MoV","D2","FS312","FS313","FS353"],
    hotwork: ["FS457","H13","DT413","5CrNiMoV"],
    plasticmold: ["FT64","FS136","FS2010","FS633","FS139","FS669"],
    others: [""],
  },
  {
    id: 9,
    name: "东北特钢（东特）",
    country: "中国",
    website: "http://www.dtsteel.com/",
    remark: "代表牌号DS136、10Ni3MnCuAl；预硬塑胶钢、压铸钢",
    coldwork: ["DL718H"],
    hotwork: ["H13"],
    plasticmold: ["DS136","DL166","D315","NAK80"],
    others: [""],
  },
  {
    id: 10,
    name: "天工国际",
    country: "中国",
    website: "https://www.tggj.cn/about1.html",
    remark: "粉末冶金模具钢、高速钢、压铸模具钢，国内头部产能",
    coldwork: ["O1","Cr12MoV","SKD11","D2","DC53","A2","S7","TGX12"],
    hotwork: ["TGPP13","TGE11","TGE21","TGE23","TGE55"],
    plasticmold: ["1.2083","440C","TGP35 ESR 1.2083改良","TGP32 ESR S136改良","TGP80R","1.2767"],
    others: ["M2","M35","M42",'喷射钢TSFD2','喷射钢TSFDC53'],
  },
  {
    id: 11,
    name: "广东雄峰特钢",
    country: "中国",
    website: "http://www.xiongfenggroup.com/mould_steel/15.html",
    remark: "华南模具钢服务商，XF系列塑胶钢，贸易+精料加工",
    coldwork: ["XFO1","XFD3M","XFD12","XFD11","XFD2","XFDC53"],
    hotwork: ["XF2343R","XF318VAR","XF328VAR","XFH13R","XFH13"],
    plasticmold: ["XF768","XFP80R","XF718","XF758","XFP81R","XF2311","XF738","XFP20"],
    others: ["XF136AD耐蚀","XF868R耐蚀","XF2085耐蚀","XF2316耐蚀","XF2083耐蚀","XF136R耐蚀"],
  },
  {
    id: 12,
    name: "舞钢（河钢集团舞阳钢铁）",
    country: "中国",
    website: "http://www.wygt.cn",
    remark: "国内宽厚钢板生产科研基地；年产钢500万吨、宽厚钢板360万吨；拥有模具钢板产品线，擅长超大超厚探伤模具钢板；起草9项国家标准，产品出口40多个国家和地区，牌号包含P20、718、1.2316、H13等",
    coldwork: ["S45C-S55C","SM45-SM55","SM3Cr2Mo","SM3Cr2Ni1Mo","2711","2738","P20","618","718","P80","638","AISI4140","DIMO42H","45-55","50Mn-65Mn","WSM136B","75Cr1","WSM30B","WSM35B","SM2312B","1.2311","1.2312","4Cr13","H13","2316","DIEVAR","NAK80","WLMA8"],
    hotwork: [""],
    plasticmold: [""],
    others: [""],
    },  
    {
    id: 201,
    name: "Finkl Steel（芬可乐）",
    country: "美国",
    website: "https://www.finkl.com",
    remark: "北美原厂钢厂，隶属Schmolz+Bickenbach集团；代表牌号MD‑Xtra、MLQ‑Xtra改良P20，FX‑XTRA热作钢，Mar‑X沉淀硬化不锈钢；国内俗称芬可乐"
  },
  {
    id: 202,
    name: "Ellwood Specialty Metals(ESM)",
    country: "美国",
    website: "https://esmusa.elwd.com",
    remark: "全流程冶炼锻造模具钢厂；ExELL‑P20M‑HH预硬塑胶钢、ExELL 15‑5PH模具不锈钢，擅长大截面锻件模具钢"
  },
  {
    id: 203,
    name: "Carpenter Technology（卡朋特）",
    country: "美国",
    website: "https://www.carpentertechnology.com",
    remark: "特种合金大厂；沉淀硬化不锈钢、粉末工具钢；模具业务为其分支，航空合金为强项"
  },
  {
    id: 204,
    name: "DME",
    country: "美国",
    website: "https://www.dme.net",
    remark: "贸易分销商，非冶炼钢厂；主营模架+模具钢精板加工，DME#7不锈钢，侧重模具配套供应链"
  },
  {
    id: 205,
    name: "International Mold Steel（IM Steel）",
    country: "美国",
    website: "https://imsteel.com",
    remark: "贸易服务商，不炼钢；专注塑胶模具钢，代理欧美日系牌号，面向北美高端注塑模具行业"
  }

]



</script>

<style scoped>
/* some style */

</style>